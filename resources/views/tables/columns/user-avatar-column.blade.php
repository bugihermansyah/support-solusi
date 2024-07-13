<div class="grid w-full px-3 py-4 fi-ta-text gap-y-1">
    <div class="flex ">
        <div class="flex max-w-max">
            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                <img src="{{ $getRecord()->user->getFirstMediaUrl('avatars', 'thumb') ?: 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($getRecord()->user->email))) . '?s=64&d=robohash&r=pg' }}"
                     alt="Avatar"
                     class="w-8 h-8 rounded-full">
                <span class="text-sm leading-6 fi-ta-text-item-label text-gray-950 dark:text-white">
                    {{ $getRecord()->user->firstname }}
                </span>
            </div>
        </div>
    </div>
</div>
