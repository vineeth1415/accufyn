@props(['property'])

<div class="group bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
    <!-- Image with Badges -->
    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
        <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
        
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent opacity-80"></div>

        <!-- Top Badges -->
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white/95 text-slate-800 backdrop-blur-md shadow-sm">
                {{ $property->property_type }}
            </span>
            @if($property->possession_status)
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-900/80 text-white backdrop-blur-md">
                    {{ $property->possession_status }}
                </span>
            @endif
            @if(!empty($property->youtube_url))
                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-red-600 text-white shadow-md flex items-center gap-1">
                    <i class="fa-brands fa-youtube text-[11px]"></i>
                    <span>Video Tour</span>
                </span>
            @endif
        </div>

        <!-- Bottom Overlay on Image: Price & Views -->
        <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between text-white z-10">
            <div>
                <div class="text-[11px] font-semibold tracking-wider uppercase text-slate-200">Price</div>
                <div class="text-xl font-extrabold tracking-tight text-white drop-shadow-sm">
                    {{ $property->formatted_price }}
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-200 bg-slate-900/60 px-2 py-0.5 rounded-full backdrop-blur-sm">
                <i class="fa-solid fa-eye text-[10px]"></i>
                <span>{{ number_format($property->views) }}</span>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="p-5 flex-1 flex flex-col justify-between">
        <div>
            <!-- Location -->
            <div class="flex items-center gap-1.5 text-xs font-semibold text-brand-600 mb-1.5">
                <i class="fa-solid fa-location-dot"></i>
                <span class="truncate">{{ $property->area }}, {{ $property->city }}</span>
            </div>

            <!-- Title -->
            <h3 class="font-bold text-slate-900 text-base leading-snug group-hover:text-brand-600 transition line-clamp-1 mb-2">
                <a href="{{ route('properties.show', $property) }}">
                    {{ $property->title }}
                </a>
            </h3>

            <!-- Specs Grid -->
            <div class="grid grid-cols-3 gap-2 py-3 border-y border-slate-100 my-3 text-xs text-slate-600">
                @if(in_array($property->property_type, ['Plot', 'Land']))
                    <div class="flex items-center gap-1.5 truncate text-emerald-700 font-semibold">
                        <i class="fa-solid fa-vector-square text-emerald-500"></i>
                        <span>Freehold Plot</span>
                    </div>
                @elseif(in_array($property->property_type, ['Commercial Space', 'Building']))
                    <div class="flex items-center gap-1.5 truncate text-indigo-700 font-semibold">
                        <i class="fa-solid fa-briefcase text-indigo-500"></i>
                        <span>Commercial</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-bed text-slate-400"></i>
                        <span>{{ $property->bhk ? $property->bhk . ' BHK' : 'Residential' }}</span>
                    </div>
                @endif

                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-ruler-combined text-slate-400"></i>
                    <span>{{ number_format($property->area_sqft) }} sq.ft</span>
                </div>

                <div class="flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-building text-slate-400"></i>
                    <span class="truncate">{{ $property->property_type }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Action -->
        <div class="flex items-center justify-between pt-1">
            <div class="text-xs text-slate-500 truncate">
                Listed by <span class="font-medium text-slate-700">{{ $property->user->name ?? 'Verified Seller' }}</span>
            </div>
            <a href="{{ route('properties.show', $property) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 group-hover:translate-x-0.5 transition">
                <span>View Details</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>
