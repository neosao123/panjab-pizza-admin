@if (Auth::guard('admin')->check())
    @php
        $code = Auth::guard('admin')->user()->code;
        $role = Auth::guard('admin')->user()->role;
        $menufile = storage_path('app/public/rights/menu.json');
        $rightfile = storage_path('app/public/rights/' . $role . '.json');
        $filecontents = file_get_contents($menufile);
        $rightscontents = file_get_contents($rightfile);
        $menuJson = json_decode($filecontents, true);
        $rightJson = json_decode($rightscontents, true);
        $rightsMenu = [];
        foreach ($rightJson as $rt) {
            array_push($rightsMenu, explode('.', $rt['menu'])[0]);
        }
        $menuSeq = array_column($menuJson, 'seq');
        array_multisort($menuSeq, SORT_ASC, $menuJson);
    @endphp
    <aside class="left-sidebar">
        <div class="scroll-sidebar">
            <nav class="sidebar-nav">
                <ul id="sidebarnav">
                    @foreach ($menuJson as $menu)
                        @if (in_array($menu['id'], $rightsMenu))
                            @if ($menu['type'] == 1)
                                <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark"
                                        href="javascript:void(0)" aria-expanded="false"><i
                                            class="{{ $menu['icon'] }}"></i><span
                                            class="hide-menu">{{ $menu['name'] }}</span></a>
                                    <ul aria-expanded="false" class="collapse  first-level">
                                        @php
                                            $submenuSeq = array_column($menu['submenu'], 'seq');
                                            array_multisort($submenuSeq, SORT_ASC, $menu['submenu']);
                                        @endphp
                                        @foreach ($menu['submenu'] as $submenu)
                                            @if (array_search($submenu['id'], array_column($rightJson, 'menu')) !== false)
                                                <li class="sidebar-item"><a href="{{ url($submenu['url']) }}"
                                                        class="sidebar-link"><i class="{{ $submenu['icon'] }}"></i><span
                                                            class="hide-menu">{{ $submenu['name'] }}</span></a></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                @php
                                    $submenu = $menu['submenu'][0];
                                @endphp
                                <li class="sidebar-item"><a class="sidebar-link waves-effect waves-dark sidebar-link"
                                        href="{{ url($submenu['url']) }}" aria-expanded="false"><i
                                            class="{{ $submenu['icon'] }}"></i><span
                                            class="hide-menu">{{ $submenu['name'] }}</span></a></li>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>
    </aside>

@endif
