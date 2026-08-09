<aside
    class="hidden
           w-[220px]
           shrink-0
           border-r border-[#E4EAE6]
           py-8 pr-5
           lg:block"
>

    <div
        class="mb-3
               px-3
               text-[10px]
               font-bold
               uppercase
               tracking-[.12em]
               text-[#98A49E]"
    >
        Buyer Menu
    </div>


    <nav class="space-y-1">

        <a
            href="{{ route('buyer.dashboard') }}"
            class="flex
                   items-center
                   gap-3
                   rounded-xl
                   bg-[#0B3D2E]
                   px-3 py-3
                   text-[13px]
                   font-semibold
                   text-white"
        >

            <i class="fa-solid fa-house w-4"></i>

            Dashboard

        </a>


        <a
            href="#"
            class="flex
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-solid fa-receipt w-4"></i>

            Transactions

        </a>


        <a
            href="#"
            class="flex
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-regular fa-bell w-4"></i>

            Notifications

        </a>


        <a
            href="{{ route('featured-businesses') }}"
            class="flex
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-solid fa-store w-4"></i>

            Featured businesses

        </a>


        <a
            href="#"
            class="flex
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-solid fa-gear w-4"></i>

            Profile settings

        </a>


        <a
            href="{{ route('support') }}"
            class="flex
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-regular fa-comments w-4"></i>

            Support

        </a>

    </nav>


    <div
        class="mb-3
               mt-8
               px-3
               text-[10px]
               font-bold
               uppercase
               tracking-[.12em]
               text-[#98A49E]"
    >
        Switch
    </div>


    <form
        method="POST"
        action="{{ route('account.switch', 'seller') }}"
    >

        @csrf


        <button
            type="submit"
            class="flex
                   w-full
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-left
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-solid fa-arrow-right-arrow-left w-4"></i>

            Seller view

        </button>

    </form>


    <form
        method="POST"
        action="{{ route('logout') }}"
    >

        @csrf


        <button
            type="submit"
            class="flex
                   w-full
                   items-center
                   gap-3
                   rounded-xl
                   px-3 py-3
                   text-left
                   text-[13px]
                   font-semibold
                   text-[#5A6660]"
        >

            <i class="fa-solid fa-arrow-right-from-bracket w-4"></i>

            Log out

        </button>

    </form>

</aside>