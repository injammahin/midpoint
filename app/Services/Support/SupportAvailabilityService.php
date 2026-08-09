<?php

namespace App\Services\Support;

use App\Models\SupportChatBlackout;
use App\Models\SupportChatSetting;
use Carbon\CarbonImmutable;

class SupportAvailabilityService
{
    public function status(): array
    {
        $settings =
            SupportChatSetting::current();


        $timezone =
            $settings->timezone
            ?: config(
                'support.timezone',
                'Africa/Lagos'
            );


        $nowUtc =
            CarbonImmutable::now(
                'UTC'
            );


        $now =
            $nowUtc->setTimezone(
                $timezone
            );


        if (!$settings->enabled) {

            return $this->offline(
                $settings,
                $now,
                'Live Support is currently disabled.'
            );

        }


        $activeDays =
            $settings->active_days
            ?: [
                1,
                2,
                3,
                4,
                5,
                6,
            ];


        /*
        |--------------------------------------------------------------------------
        | Day Availability
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $now->isoWeekday(),
                $activeDays
            )
        ) {

            return $this->offline(
                $settings,
                $now,
                $settings->offline_message
            );

        }


        $open =
            $this->dateTime(
                $now,
                $settings->opens_at,
                $timezone
            );


        $close =
            $this->dateTime(
                $now,
                $settings->closes_at,
                $timezone
            );


        /*
        |--------------------------------------------------------------------------
        | Opening Hours
        |--------------------------------------------------------------------------
        */

        if (
            $now->lt($open)
            ||
            $now->gte($close)
        ) {

            return $this->offline(
                $settings,
                $now,
                $settings->offline_message
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Temporary Blackout
        |--------------------------------------------------------------------------
        */

        $blackout =
            $this->blackoutAt(
                $nowUtc
            );


        if ($blackout) {

            return $this->offline(
                $settings,
                $now,
                $blackout->reason
                    ?: $settings->offline_message
            );

        }


        return [

            'available' =>
                true,

            'message' =>
                'Live Support is available now.',

            'next_available_at' =>
                null,

            'next_available_label' =>
                null,

            'timezone' =>
                $timezone,

            'opens_at' =>
                $settings->opens_at,

            'closes_at' =>
                $settings->closes_at,

        ];
    }


    private function offline(
        SupportChatSetting $settings,
        CarbonImmutable $now,
        ?string $message
    ): array {

        $next =
            $this->findNextOpening(
                $settings,
                $now
            );


        return [

            'available' =>
                false,

            'message' =>
                $message
                ?: 'Live Support is currently unavailable.',

            'next_available_at' =>
                $next
                    ? $next->toIso8601String()
                    : null,

            'next_available_label' =>
                $next
                    ? $next->format(
                        'l, M j \a\t g:i A'
                    )
                    : null,

            'timezone' =>
                $settings->timezone,

            'opens_at' =>
                $settings->opens_at,

            'closes_at' =>
                $settings->closes_at,

        ];
    }


    private function findNextOpening(
        SupportChatSetting $settings,
        CarbonImmutable $now
    ): ?CarbonImmutable {

        $timezone =
            $settings->timezone;


        $days =
            $settings->active_days
            ?: [
                1,
                2,
                3,
                4,
                5,
                6,
            ];


        /*
         * Search up to two weeks.
         */
        for (
            $dayOffset = 0;
            $dayOffset <= 14;
            $dayOffset++
        ) {

            $date =
                $now
                    ->startOfDay()
                    ->addDays(
                        $dayOffset
                    );


            if (
                !in_array(
                    $date->isoWeekday(),
                    $days
                )
            ) {

                continue;

            }


            $open =
                $this->dateTime(
                    $date,
                    $settings->opens_at,
                    $timezone
                );


            $close =
                $this->dateTime(
                    $date,
                    $settings->closes_at,
                    $timezone
                );


            if ($dayOffset === 0) {

                if ($now->gte($close)) {
                    continue;
                }


                $candidate =
                    $now->lt($open)
                        ? $open
                        : $now;

            } else {

                $candidate =
                    $open;

            }


            /*
             * Candidate may fall inside a blackout.
             * Move candidate to blackout end.
             */
            for (
                $safety = 0;
                $safety < 10;
                $safety++
            ) {

                $candidateUtc =
                    $candidate
                        ->setTimezone(
                            'UTC'
                        );


                $blackout =
                    $this->blackoutAt(
                        $candidateUtc
                    );


                if (!$blackout) {
                    break;
                }


                $candidate =
                    CarbonImmutable::parse(
                        $blackout->ends_at
                    )
                    ->setTimezone(
                        $timezone
                    );

            }


            if ($candidate->lt($close)) {

                return $candidate;

            }

        }


        return null;
    }


    private function blackoutAt(
        CarbonImmutable $utc
    ): ?SupportChatBlackout {

        return SupportChatBlackout::query()

            ->where(
                'is_active',
                true
            )

            ->where(
                'starts_at',
                '<=',
                $utc
            )

            ->where(
                'ends_at',
                '>',
                $utc
            )

            ->first();
    }


    private function dateTime(
        CarbonImmutable $date,
        string $time,
        string $timezone
    ): CarbonImmutable {

        return CarbonImmutable::parse(

            $date->format(
                'Y-m-d'
            )
            .
            ' '
            .
            $time,

            $timezone

        );
    }
}