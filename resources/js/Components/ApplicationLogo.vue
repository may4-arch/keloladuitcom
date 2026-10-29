<template>
    <div class="inline-flex items-center justify-center">
        <svg
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="h-full w-full drop-shadow-[0_0_12px_rgba(16,185,129,0.35)]"
        >
            <rect width="48" height="48" rx="12" fill="url(#brand-gradient)" />
            <!-- Wallet / Finance stylized icon -->
            <path
                d="M13 18C13 16.3431 14.3431 15 16 15H31C32.1046 15 33 15.8954 33 17V19H16C14.3431 19 13 20.3431 13 22V18Z"
                fill="white"
                fill-opacity="0.6"
            />
            <rect
                x="13"
                y="19"
                width="22"
                height="15"
                rx="3"
                fill="white"
            />
            <rect
                x="26"
                y="23"
                width="9"
                height="7"
                rx="2"
                fill="#064E3B"
            />
            <circle cx="30.5" cy="26.5" r="1.5" fill="#34D399" />
            <defs>
                <linearGradient
                    id="brand-gradient"
                    x1="0"
                    y1="0"
                    x2="48"
                    y2="48"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop stop-color="#10B981" />
                    <stop offset="1" stop-color="#0F766E" />
                </linearGradient>
            </defs>
        </svg>
    </div>
</template>
