<x-filament-panels::page>
    <div class="fi-dashboard-grid space-y-10">

        @php $chunks = array_chunk($sections, 3); @endphp

        @foreach($chunks as $index => $chunk)
            <div class="flex flex-row gap-x-8">
                @foreach($chunk as $section)
                    <div class="fi-dashboard-block flex flex-col flex-1 rounded-2xl p-6
                                bg-white dark:bg-gray-800
                                shadow-md dark:shadow-gray-900
                                border border-gray-100 dark:border-gray-700">

                     
                        <div class="flex items-center justify-between mb-4 border-b-2 pb-2
                            @if(($section['color'] ?? '') === 'teal') border-teal-500
                            @elseif(($section['color'] ?? '') === 'orange') border-orange-500
                            @elseif(($section['color'] ?? '') === 'primary') border-primary-500
                            @elseif(($section['color'] ?? '') === 'warning') border-amber-500
                            @else border-violet-500
                            @endif">

                            <h3 @class([
                                'text-base font-extrabold uppercase tracking-widest',
                                'text-teal-600 dark:text-teal-400' => ($section['color'] ?? '') === 'teal',
                                'text-orange-500 dark:text-orange-400' => ($section['color'] ?? '') === 'orange',
                                'text-primary-600 dark:text-primary-400' => ($section['color'] ?? '') === 'primary',
                                'text-amber-600 dark:text-amber-400' => ($section['color'] ?? '') === 'warning',
                                'text-violet-600 dark:text-violet-400' => ($section['color'] ?? '') === 'purple',
                            ])>
                                {{ $section['title'] }}
                            </h3>

                            @if(isset($section['count']))
                                <span @class([
                                    'text-xs font-bold px-2 py-0.5 rounded-full',
                                    'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300' => ($section['color'] ?? '') === 'teal',
                                    'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300' => ($section['color'] ?? '') === 'orange',
                                    'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' => ($section['color'] ?? '') === 'primary',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' => ($section['color'] ?? '') === 'warning',
                                    'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300' => ($section['color'] ?? '') === 'purple',
                                ])>
                                    {{ $section['count'] }}
                                </span>
                            @endif
                        </div>

                        <ul class="mt-2 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                            @foreach($section['items'] as $item)
                                <li>
                                    <a href="{{ $item['url'] ?? '#' }}"
                                       class="flex items-center justify-between gap-2 tracking-wide
                                              hover:text-primary-600 dark:hover:text-primary-400
                                              transition-colors duration-200">

                                        <span class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                                @if(($section['color'] ?? '') === 'teal') bg-teal-400
                                                @elseif(($section['color'] ?? '') === 'orange') bg-orange-400
                                                @elseif(($section['color'] ?? '') === 'primary') bg-primary-400
                                                @elseif(($section['color'] ?? '') === 'warning') bg-amber-400
                                                @else bg-violet-400
                                                @endif">
                                            </span>
                                            {{ $item['label'] }}
                                        </span>

                                        @if(isset($item['count']))
                                            <span class="inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 text-xs font-bold rounded-full
                                                @if(($section['color'] ?? '') === 'teal') bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300
                                                @elseif(($section['color'] ?? '') === 'orange') bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300
                                                @elseif(($section['color'] ?? '') === 'primary') bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300
                                                @elseif(($section['color'] ?? '') === 'warning') bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300
                                                @else bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300
                                                @endif">
                                                {{ $item['count'] }}
                                            </span>
                                        @endif

                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @if($index < count($chunks) - 1)
                <hr class="border-gray-200 dark:border-gray-700">
            @endif

        @endforeach

    </div>
</x-filament-panels::page>