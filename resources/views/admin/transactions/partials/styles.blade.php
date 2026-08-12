<style>

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    .txn-admin-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }


    .txn-admin-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }


    .txn-admin-heading h2 {
        margin: 0;
        color: var(--admin-heading);
        font-size: 22px;
        font-weight: 700;
    }


    .txn-admin-heading p {
        margin: 5px 0 0;
        color: var(--admin-muted);
        font-size: 12px;
        line-height: 1.6;
    }



    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */

    .txn-admin-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }


    .txn-stat {
        padding: 20px;
    }


    .txn-stat-icon {
        display: grid;
        width: 40px;
        height: 40px;
        margin-bottom: 14px;
        place-items: center;
        border-radius: 11px;
        background: rgba(14, 165, 132, .10);
        color: #0EA584;
        font-size: 16px;
    }


    .txn-stat.is-warning .txn-stat-icon {
        background: rgba(245, 158, 11, .12);
        color: #D97706;
    }


    .txn-stat.is-purple .txn-stat-icon {
        background: rgba(124, 92, 255, .11);
        color: #7557FF;
    }


    .txn-stat.is-success .txn-stat-icon {
        background: rgba(18, 183, 106, .10);
        color: #12B76A;
    }


    .txn-stat span {
        display: block;
        color: var(--admin-muted);
        font-size: 11px;
    }


    .txn-stat strong {
        display: block;
        margin-top: 6px;
        color: var(--admin-heading);
        font-family: "Bricolage Grotesque", sans-serif;
        font-size: 25px;
        font-weight: 700;
    }



    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    .txn-filter-card {
        padding: 16px;
    }


    .txn-filters {
        display: grid;
        grid-template-columns:
            minmax(190px, 1.5fr)
            repeat(5, minmax(125px, 1fr))
            auto
            auto;
        gap: 10px;
        align-items: center;
    }


    .txn-filters input,
    .txn-filters select {
        width: 100%;
        min-height: 40px;
        padding: 0 11px;
        border: 1px solid var(--admin-border);
        border-radius: 9px;
        outline: 0;
        background: var(--admin-card);
        color: var(--admin-text);
        font: inherit;
        font-size: 11px;
    }


    .txn-filters input:focus,
    .txn-filters select:focus {
        border-color: #0EA584;
        box-shadow: 0 0 0 3px rgba(14,165,132,.08);
    }


    .txn-filter-btn,
    .txn-clear-btn,
    .txn-action-btn {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 9px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }


    .txn-filter-btn {
        border: 0;
        background: #0B8065;
        color: #FFFFFF;
        cursor: pointer;
    }


    .txn-clear-btn {
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
    }


    .txn-action-btn {
        min-height: 34px;
        border: 1px solid var(--admin-border);
        color: var(--admin-heading);
    }


    .txn-action-btn:hover,
    .txn-clear-btn:hover {
        border-color: #0EA584;
        color: #0B8065;
    }



    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .txn-table-card {
        overflow: hidden;
    }


    .txn-table-scroll {
        overflow-x: auto;
    }


    .txn-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }


    .txn-table th {
        padding: 13px 15px;
        border-bottom: 1px solid var(--admin-border);
        background: var(--admin-subtle);
        color: var(--admin-muted);
        text-align: left;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
    }


    .txn-table td {
        padding: 15px;
        border-bottom: 1px solid var(--admin-border);
        color: var(--admin-text);
        font-size: 11px;
        vertical-align: middle;
    }


    .txn-table tbody tr:last-child td {
        border-bottom: 0;
    }


    .txn-table tbody tr:hover {
        background: rgba(14,165,132,.025);
    }


    .txn-table strong {
        display: block;
        color: var(--admin-heading);
        font-size: 11px;
    }


    .txn-table small {
        display: block;
        margin-top: 4px;
        color: var(--admin-muted);
        font-size: 9px;
    }



    /*
    |--------------------------------------------------------------------------
    | Badge
    |--------------------------------------------------------------------------
    */

    .txn-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }


    .txn-badge.green {
        background: #ECFDF3;
        color: #067647;
    }


    .txn-badge.yellow {
        background: #FFF7E8;
        color: #B54708;
    }


    .txn-badge.red {
        background: #FFF1F2;
        color: #B42318;
    }


    .txn-badge.blue {
        background: #EEF4FF;
        color: #3538CD;
    }


    .txn-badge.purple {
        background: #F2F0FF;
        color: #6941C6;
    }


    .txn-badge.gray {
        background: var(--admin-subtle);
        color: var(--admin-muted);
    }



    /*
    |--------------------------------------------------------------------------
    | Details
    |--------------------------------------------------------------------------
    */

    .txn-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(310px, .75fr);
        gap: 18px;
        align-items: start;
    }


    .txn-detail-stack {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }


    .txn-detail-card {
        padding: 22px;
    }


    .txn-detail-card h3 {
        margin: 0 0 17px;
        color: var(--admin-heading);
        font-size: 15px;
        font-weight: 700;
    }


    .txn-detail-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 10px 0;
        border-bottom: 1px solid var(--admin-border);
    }


    .txn-detail-row:last-child {
        border-bottom: 0;
    }


    .txn-detail-row > span {
        color: var(--admin-muted);
        font-size: 10px;
    }


    .txn-detail-row > strong,
    .txn-detail-row > div {
        color: var(--admin-heading);
        text-align: right;
        font-size: 11px;
        font-weight: 600;
    }



    /*
    |--------------------------------------------------------------------------
    | Participants
    |--------------------------------------------------------------------------
    */

    .txn-party-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }


    .txn-party {
        padding: 15px;
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        background: var(--admin-subtle);
    }


    .txn-party-label {
        margin-bottom: 7px;
        color: var(--admin-muted);
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
    }


    .txn-party strong {
        display: block;
        color: var(--admin-heading);
        font-size: 12px;
    }


    .txn-party span {
        display: block;
        margin-top: 4px;
        color: var(--admin-muted);
        font-size: 10px;
    }



    /*
    |--------------------------------------------------------------------------
    | Alert
    |--------------------------------------------------------------------------
    */

    .txn-dispute-alert {
        padding: 17px;
        border: 1px solid #FECDD3;
        border-radius: 13px;
        background: #FFF6F6;
    }


    .txn-dispute-alert strong {
        color: #B42318;
        font-size: 12px;
    }


    .txn-dispute-alert p {
        margin: 7px 0 0;
        color: #7A4540;
        font-size: 10px;
        line-height: 1.6;
    }



    /*
    |--------------------------------------------------------------------------
    | Evidence
    |--------------------------------------------------------------------------
    */

    .dispute-evidence-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }


    .dispute-evidence-item {
        overflow: hidden;
        min-height: 110px;
        border: 1px solid var(--admin-border);
        border-radius: 11px;
        background: var(--admin-subtle);
    }


    .dispute-evidence-item img {
        display: block;
        width: 100%;
        height: 150px;
        object-fit: cover;
    }


    .dispute-evidence-file {
        display: flex;
        min-height: 110px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 15px;
        color: var(--admin-heading);
        text-decoration: none;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
    }



    /*
    |--------------------------------------------------------------------------
    | Empty
    |--------------------------------------------------------------------------
    */

    .txn-empty {
        padding: 55px 20px;
        text-align: center;
    }


    .txn-empty i {
        color: #0EA584;
        font-size: 28px;
    }


    .txn-empty strong {
        display: block;
        margin-top: 14px;
        color: var(--admin-heading);
        font-size: 14px;
    }


    .txn-empty span {
        display: block;
        margin-top: 7px;
        color: var(--admin-muted);
        font-size: 10px;
    }



    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 1200px) {

        .txn-filters {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

    }


    @media(max-width: 950px) {

        .txn-admin-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }


        .txn-detail-grid {
            grid-template-columns: 1fr;
        }

    }


    @media(max-width: 650px) {

        .txn-admin-stats,
        .txn-filters,
        .txn-party-grid,
        .dispute-evidence-grid {
            grid-template-columns: 1fr;
        }


        .txn-admin-heading {
            flex-direction: column;
        }

    }

</style>