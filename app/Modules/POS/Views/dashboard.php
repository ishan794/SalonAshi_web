<?php
$catMap = [0 => 'Uncategorized'];
foreach ($categories as $c) $catMap[(int)$c['id']] = $c['name'];

$customerOptions = array_map(fn($c) => [
    'value' => (int)$c['id'],
    'label' => $c['full_name'],
    'desc'  => phone_local($c['mobile']) . ($c['email'] ? ' · ' . $c['email'] : ''),
], $customers);
$staffJs = array_map(fn($s) => ['id' => (int)$s['id'], 'name' => $s['full_name'], 'role' => $s['role'] ?: 'Staff'], $staff);
$serviceJs = array_map(fn($s) => [
    'id'        => (int)$s['id'],
    'name'      => $s['name'],
    'category'  => $catMap[(int)$s['category_id']] ?? 'Uncategorized',
    'duration'  => (int)$s['duration_min'],
    'price'     => (float)$s['price'],
    'tax_pct'   => (float)$s['tax_pct'],
], $services);
?>
<div x-data='pos()' @combobox-change.window="if ($event.detail.name === 'customer_id_picker') customerId = parseInt($event.detail.value) || 0" class="-m-4 sm:-m-6 lg:-m-8">

    <!-- Top toolbar -->
    <div class="sticky top-16 z-30 border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                <span class="flex size-9 items-center justify-center rounded-lg bg-brand-600 text-white shadow-sm shadow-brand-600/30">
                    <i data-lucide="scan-line" class="size-5"></i>
                </span>
                <div>
                    <p class="text-base font-semibold">POS</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Quick book or bill walk-ins</p>
                </div>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <a href="<?= site_url('admin/customers/create') ?>" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                    <i data-lucide="user-plus" class="size-3.5"></i> Add customer
                </a>
                <button type="button" @click="resetCart()" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                    <i data-lucide="rotate-ccw" class="size-3.5"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Toasts -->
    <div x-show="toast" x-cloak x-transition class="fixed top-20 right-6 z-50">
        <div :class="toastOk ? 'bg-green-600' : 'bg-red-600'" class="rounded-md px-4 py-2 text-sm font-medium text-white shadow-lg shadow-black/10">
            <span x-text="toast"></span>
        </div>
    </div>

    <!-- Body: 3-column on desktop -->
    <div class="grid grid-cols-1 md:grid-cols-[1fr_360px] gap-0 min-h-[calc(100vh-8rem)]">

        <!-- ───── Left: customer + service grid ───── -->
        <div class="p-4 sm:p-6 lg:p-8 space-y-5 overflow-auto">

            <!-- Customer -->
            <div class="rounded-xl bg-white dark:bg-gray-800 p-5 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="user-round" class="size-4 text-brand-600 dark:text-brand-400"></i>
                        Customer
                    </h3>
                    <span x-show="customerId" x-cloak class="text-xs text-green-600 dark:text-green-400 inline-flex items-center gap-1">
                        <i data-lucide="check" class="size-3"></i> selected
                    </span>
                </div>
                <?= view('components/form/combobox', [
                    'name'        => 'customer_id_picker',
                    'options'     => $customerOptions,
                    'placeholder' => 'Search by name, mobile (no country code needed)…',
                    'emptyText'   => 'No matches — use Add customer above',
                    'icon'        => 'search',
                ]) ?>
            </div>

            <!-- Category filter chips -->
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="catFilter = ''; page = 1"
                        :class="catFilter === '' ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/20' : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10'"
                        class="rounded-full px-3 py-1 text-xs font-medium transition-colors">All</button>
                <?php foreach ($categories as $c): ?>
                    <button type="button" @click="catFilter = '<?= esc($c['name']) ?>'; page = 1"
                            :class="catFilter === '<?= esc($c['name']) ?>' ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/20' : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10'"
                            class="rounded-full px-3 py-1 text-xs font-medium transition-colors">
                        <?= esc($c['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Service Search -->
            <div class="relative max-w-xs">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                    <i data-lucide="search" class="size-3.5 text-gray-400"></i>
                </div>
                <input type="text" x-model="serviceQuery" @input="page = 1" placeholder="Search services..." class="block w-full rounded-md border-gray-300 dark:border-white/10 pl-8 pr-3 py-1 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-brand-500 dark:bg-white/5">
            </div>

            <!-- Service grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <template x-for="s in paginatedServices()" :key="s.id">
                    <button type="button" @click="addService(s)"
                            class="group relative rounded-xl bg-white dark:bg-gray-800 p-4 text-left ring-1 ring-gray-200 dark:ring-white/10 shadow-sm hover:shadow-md hover:ring-brand-300 dark:hover:ring-brand-500/40 transition">
                        <p class="text-xs font-medium text-brand-600 dark:text-brand-400 uppercase tracking-wide truncate" x-text="s.category"></p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 min-h-[2.5rem]" x-text="s.name"></p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="s.duration + ' min'"></p>
                        <p class="mt-1 text-base font-bold text-gray-900 dark:text-white" x-text="'LKR ' + s.price.toLocaleString()"></p>
                        <span class="absolute right-2 top-2 size-6 rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300 group-hover:bg-brand-600 group-hover:text-white flex items-center justify-center transition">
                            <i data-lucide="plus" class="size-3.5"></i>
                        </span>
                    </button>
                </template>
            </div>

            <!-- Service Pagination -->
            <template x-if="totalPages() > 1">
                <div class="flex items-center justify-between border-t border-gray-100 dark:border-white/5 pt-4 mt-2">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Showing page <span class="font-medium text-gray-950 dark:text-white" x-text="page"></span> of <span class="font-medium text-gray-950 dark:text-white" x-text="totalPages()"></span>
                    </div>
                    <div class="inline-flex gap-2">
                        <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                                class="rounded bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10 flex items-center gap-1">
                            Previous
                        </button>
                        <button type="button" @click="page = Math.min(totalPages(), page + 1)" :disabled="page === totalPages()"
                                class="rounded bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10 flex items-center gap-1">
                            Next
                        </button>
                    </div>
                </div>
            </template>
            <p x-show="!filteredServices().length" class="text-sm text-gray-500 dark:text-gray-400 text-center py-10">No services in this category.</p>
        </div>

        <!-- ───── Right: cart ───── -->
        <aside class="bg-gray-50 dark:bg-gray-900 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-white/10 flex flex-col">

            <div class="p-5 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="size-4 text-brand-600 dark:text-brand-400"></i>
                    Cart <span class="text-gray-500 dark:text-gray-400 font-normal" x-text="cart.length ? '(' + cart.length + ')' : ''"></span>
                </h3>
            </div>

            <div class="flex-1 overflow-auto p-3 space-y-2">
                <div x-show="!cart.length" class="text-center text-gray-500 dark:text-gray-400 py-12">
                    <div class="mx-auto size-12 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center">
                        <i data-lucide="package-open" class="size-6"></i>
                    </div>
                    <p class="mt-3 text-sm font-medium">Tap a service to add</p>
                </div>

                <template x-for="(it, i) in cart" :key="i">
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-3 ring-1 ring-gray-200 dark:ring-white/10">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="it.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="it.duration + ' min · LKR ' + it.price.toLocaleString()"></p>
                            </div>
                            <button type="button" @click="cart.splice(i,1)" class="shrink-0 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                <i data-lucide="x" class="size-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Totals + actions -->
            <div class="p-5 border-t border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 space-y-2">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Items</span><span x-text="cart.length"></span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Total duration</span><span x-text="totalDuration + ' min'"></span>
                </div>
                <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-white/10">
                    <span>Total</span><span x-text="'LKR ' + total.toLocaleString()"></span>
                </div>

                <div class="grid grid-cols-1 gap-2 pt-3">
                    <button type="button" @click="openBook()" :disabled="!canBook" class="inline-flex items-center justify-center gap-2 rounded-md bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed dark:disabled:bg-white/10 dark:disabled:text-gray-500">
                        <i data-lucide="calendar-check-2" class="size-4"></i> Book Appointment
                    </button>

                    <div class="grid grid-cols-3 gap-2" x-show="canBill">
                        <button type="button" @click="bill('cash')" class="inline-flex items-center justify-center gap-1 rounded-md bg-green-600 px-2 py-2.5 text-xs font-semibold text-white hover:bg-green-700">
                            <i data-lucide="banknote" class="size-3.5"></i> Cash
                        </button>
                        <button type="button" @click="bill('card')" class="inline-flex items-center justify-center gap-1 rounded-md bg-blue-600 px-2 py-2.5 text-xs font-semibold text-white hover:bg-blue-700">
                            <i data-lucide="credit-card" class="size-3.5"></i> Card
                        </button>
                        <button type="button" @click="bill('bank_transfer')" class="inline-flex items-center justify-center gap-1 rounded-md bg-indigo-600 px-2 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700">
                            <i data-lucide="building-2" class="size-3.5"></i> Bank
                        </button>
                    </div>
                </div>

                <p x-show="!customerId" class="text-[11px] text-amber-600 dark:text-amber-400 text-center pt-1">Pick a customer to enable actions.</p>
            </div>
        </aside>
    </div>

    <!-- ── Booking modal: pick date + available time slot ── -->
    <div x-show="bookModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="bookModal = false">
        <div class="fixed inset-0 bg-gray-900/70 dark:bg-gray-950/85 backdrop-blur-sm" @click="bookModal = false"></div>

        <div class="relative w-full max-w-2xl rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-gray-200 dark:ring-white/10"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <!-- Header -->
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 dark:border-white/10 px-5 py-3.5">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="flex size-9 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                        <i data-lucide="calendar-clock" class="size-4"></i>
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pick a date &amp; time</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            <span x-text="cart.length"></span> service(s) ·
                            <span x-text="totalDuration + ' min total'"></span> ·
                            with <span class="font-medium" x-text="bookStaffName"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="bookModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <i data-lucide="x" class="size-5"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-5 max-h-[70vh] overflow-auto">

                <!-- Stylist (moved into modal) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Stylist <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                        <template x-for="st in staffList" :key="st.id">
                            <button type="button"
                                    @click="staffId = st.id; loadSlots()"
                                    :class="staffId === st.id ? 'bg-brand-50 ring-2 ring-brand-500 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10'"
                                    class="flex items-center gap-2 rounded-md px-2.5 py-2 text-left text-sm transition-colors">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-purple-500 text-white text-xs font-semibold" x-text="st.name.charAt(0).toUpperCase()"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium" x-text="st.name"></span>
                                    <span class="block truncate text-[10px] text-gray-500 dark:text-gray-400" x-text="st.role"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Date picker -->
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px] relative">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <button type="button" @click="toggleDp()"
                                class="mt-1 flex w-full items-center gap-2 rounded-md bg-white py-1.5 px-3 text-left text-sm text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-brand-500">
                            <i data-lucide="calendar" class="size-4 text-gray-400 dark:text-gray-500"></i>
                            <span class="flex-1 truncate" :class="!bookDate && 'text-gray-400 dark:text-gray-500'" x-text="bookDate ? dpFormatted() : 'Pick a date'"></span>
                            <i data-lucide="chevron-down" class="size-4 text-gray-400 transition" :class="dpOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="dpOpen" x-cloak x-transition
                             @click.outside="dpOpen = false"
                             class="absolute z-50 mt-1.5 w-72 origin-top-left rounded-lg bg-white p-3 shadow-xl ring-1 ring-gray-900/10 dark:bg-gray-800 dark:ring-white/10">
                            <div class="flex items-center justify-between px-1 pb-2">
                                <button type="button" @click="dpPrev()" class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white"><i data-lucide="chevron-left" class="size-4"></i></button>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="dpMonthLabel()"></span>
                                <button type="button" @click="dpNext()" class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white"><i data-lucide="chevron-right" class="size-4"></i></button>
                            </div>
                            <div class="grid grid-cols-7 text-center text-[10px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500 pb-1">
                                <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
                            </div>
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="(c, i) in dpCells()" :key="i">
                                    <button type="button"
                                            x-show="c"
                                            @click="dpPick(c)"
                                            :disabled="dpDisabled(c)"
                                            :class="dpCellClass(c)"
                                            class="aspect-square rounded-md text-sm font-medium transition-colors"
                                            x-text="c ? +c.split('-')[2] : ''"></button>
                                </template>
                            </div>
                            <div class="mt-2.5 flex items-center justify-end border-t border-gray-100 dark:border-white/10 pt-2.5">
                                <button type="button" @click="dpToday()" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">Today</button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="(label, days) in {0:'Today', 1:'Tomorrow', 2:'In 2 days', 7:'Next week'}" :key="days">
                            <button type="button" @click="setRelativeDay(parseInt(days)); loadSlots()"
                                    :class="bookDate === relativeDateISO(parseInt(days)) ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10'"
                                    class="rounded-md px-2.5 py-1 text-xs font-medium transition-colors" x-text="label"></button>
                        </template>
                    </div>
                </div>

                <p x-show="bookHoursLabel" class="text-xs text-gray-500 dark:text-gray-400">
                    Working hours: <strong class="text-gray-700 dark:text-gray-300" x-text="bookHoursLabel"></strong>
                </p>

                <!-- Slots -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Available slots</label>
                        <span x-show="loadingSlots" x-cloak class="text-xs text-gray-500 dark:text-gray-400 inline-flex items-center gap-1">
                            <i data-lucide="loader-2" class="size-3.5 animate-spin"></i> loading…
                        </span>
                    </div>

                    <div x-show="!loadingSlots && !slots.length" class="py-8 text-center">
                        <div class="mx-auto size-10 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                            <i x-show="!staffId" data-lucide="user" class="size-5 text-gray-400 dark:text-gray-500"></i>
                            <i x-show="staffId" data-lucide="clock-alert" class="size-5 text-gray-400 dark:text-gray-500"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="!staffId ? 'Select a stylist to view available slots.' : 'No slots returned. Set staff working hours under Staff.'"></p>
                    </div>

                    <div x-show="!loadingSlots && slots.length" class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-6 gap-1.5">
                        <template x-for="s in slots" :key="s.time">
                            <button type="button"
                                    @click="if (s.available) bookSlot = s.time"
                                    :disabled="!s.available"
                                    :title="s.available ? 'Available' : (s.reason === 'past' ? 'Past time' : 'Already booked')"
                                    :class="!s.available
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed line-through dark:bg-white/5 dark:text-gray-600'
                                        : bookSlot === s.time
                                            ? 'bg-brand-600 text-white ring-2 ring-brand-300 dark:ring-brand-500/40 shadow-sm shadow-brand-600/30'
                                            : 'bg-white text-gray-900 ring-1 ring-gray-300 hover:bg-brand-50 hover:ring-brand-300 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:hover:bg-brand-500/10'"
                                    class="rounded-md px-2 py-2 text-xs font-mono font-semibold transition-colors"
                                    x-text="s.time"></button>
                        </template>
                    </div>

                    <div x-show="!loadingSlots && slots.length" class="mt-3 flex gap-3 text-[10px] text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-white ring-1 ring-gray-300 dark:bg-white/5 dark:ring-white/10"></span> Free</span>
                        <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-brand-600"></span> Selected</span>
                        <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-gray-100 dark:bg-white/10"></span> Booked / past</span>
                    </div>
                </div>

                <!-- Summary -->
                <div x-show="bookSlot" x-cloak class="rounded-lg bg-brand-50 dark:bg-brand-500/10 p-4 ring-1 ring-brand-200 dark:ring-brand-500/20">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="check-circle-2" class="size-5 text-brand-600 dark:text-brand-400 mt-0.5 shrink-0"></i>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Booking <strong class="text-gray-900 dark:text-white" x-text="bookDateLabel + ' at ' + bookSlot"></strong>
                            with <strong class="text-gray-900 dark:text-white" x-text="bookStaffName"></strong>.
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ends approx. <span x-text="bookEndTime"></span> (<span x-text="totalDuration"></span> min).</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2 border-t border-gray-200 dark:border-white/10 px-5 py-3 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                <button type="button" @click="bookModal = false" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                    Cancel
                </button>
                <button type="button" @click="confirmBook()" :disabled="!canConfirmBook || submitting"
                        class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed dark:disabled:bg-white/10">
                    <i x-show="!submitting" data-lucide="check" class="size-4"></i>
                    <i x-show="submitting" x-cloak data-lucide="loader-2" class="size-4 animate-spin"></i>
                    <span x-text="submitting ? 'Booking…' : 'Confirm booking'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function pos() {
    return {
        customerId: 0,
        staffId: 0,
        cart: [],
        catFilter: '',
        serviceQuery: '',
        page: 1,
        perPage: 12,
        services: <?= json_encode(array_values($serviceJs), JSON_UNESCAPED_UNICODE) ?>,
        staffList: <?= json_encode(array_values($staffJs), JSON_UNESCAPED_UNICODE) ?>,
        toast: '',
        toastOk: true,
        csrfName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',

        // Booking modal state
        bookModal: false,
        bookDate: '',
        bookSlot: '',
        slots: [],
        bookHoursLabel: '',
        loadingSlots: false,
        submitting: false,

        init() {},

        filteredServices() {
            let res = this.services;
            if (this.catFilter) {
                res = res.filter(s => s.category === this.catFilter);
            }
            if (this.serviceQuery) {
                const q = this.serviceQuery.toLowerCase().trim();
                res = res.filter(s => s.name.toLowerCase().includes(q));
            }
            return res;
        },
        totalPages() {
            return Math.ceil(this.filteredServices().length / this.perPage);
        },
        paginatedServices() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredServices().slice(start, start + this.perPage);
        },
        addService(s) {
            this.cart.push({ id: s.id, name: s.name, duration: s.duration, price: s.price, tax_pct: s.tax_pct });
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
        get total() {
            return this.cart.reduce((t, i) => t + i.price + (i.price * i.tax_pct / 100), 0);
        },
        get totalDuration() {
            return this.cart.reduce((t, i) => t + i.duration, 0);
        },
        // Cart-button gating: stylist now selected inside the modal, so only customer + cart needed here
        get canBook() {
            return this.customerId > 0 && this.cart.length > 0;
        },
        get canBill() {
            return this.customerId > 0 && this.cart.length > 0;
        },
        // Modal confirm gating (after stylist is picked in the modal)
        get canConfirmBook() {
            return this.canBook && this.staffId > 0 && this.bookDate && this.bookSlot;
        },

        // ── Date picker (lives in pos() scope so reactivity works cleanly inside the modal) ──
        dpOpen: false,
        dpCursor: new Date().toISOString().slice(0,7),
        dpFormatted() {
            if (! this.bookDate) return '';
            return new Date(this.bookDate + 'T00:00').toLocaleDateString(undefined, { weekday:'short', year:'numeric', month:'short', day:'numeric' });
        },
        dpMonthLabel() {
            return new Date(this.dpCursor + '-01T00:00').toLocaleDateString(undefined, { year:'numeric', month:'long' });
        },
        dpCells() {
            const [y, m] = this.dpCursor.split('-').map(Number);
            const dow  = new Date(y, m - 1, 1).getDay();
            const days = new Date(y, m, 0).getDate();
            const out = [];
            for (let i = 0; i < dow; i++) out.push(null);
            for (let d = 1; d <= days; d++)
                out.push(`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
            while (out.length < 42) out.push(null);
            return out;
        },
        dpDisabled(d) {
            if (!d) return true;
            return d < this.todayISO;
        },
        dpCellClass(d) {
            if (!d) return '';
            if (this.bookDate === d) return 'bg-brand-600 text-white shadow-sm shadow-brand-600/30';
            if (this.dpDisabled(d)) return 'text-gray-300 dark:text-gray-600 cursor-not-allowed';
            if (d === this.todayISO) return 'ring-1 ring-brand-300 dark:ring-brand-500/40 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10';
            return 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5';
        },
        toggleDp() {
            this.dpOpen = !this.dpOpen;
            if (this.dpOpen && this.bookDate) this.dpCursor = this.bookDate.slice(0,7);
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
        dpPick(d) {
            if (this.dpDisabled(d)) return;
            this.bookDate = d;
            this.dpOpen = false;
            this.loadSlots();
        },
        dpToday() {
            const t = this.todayISO;
            this.dpPick(t);
        },
        dpPrev() {
            const [y, m] = this.dpCursor.split('-').map(Number);
            const d = new Date(y, m - 2, 1);
            this.dpCursor = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
        dpNext() {
            const [y, m] = this.dpCursor.split('-').map(Number);
            const d = new Date(y, m, 1);
            this.dpCursor = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
        resetCart() {
            this.cart = [];
            this.staffId = 0;
            this.toast = ''; this.toastOk = true;
        },
        flash(msg, ok = true) {
            this.toast = msg; this.toastOk = ok;
            setTimeout(() => this.toast = '', 3500);
        },
        async post(url, body) {
            const fd = new FormData();
            fd.append(this.csrfName, this.csrfHash);
            for (const k in body) {
                if (Array.isArray(body[k])) body[k].forEach(v => fd.append(k + '[]', v));
                else if (body[k] !== undefined && body[k] !== null) fd.append(k, body[k]);
            }
            const r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await r.json().catch(() => ({ ok: false, msg: 'Bad server response' }));
            // Update CSRF hash from response header if present (CI4 rotates it)
            const newHash = r.headers.get('X-CSRF-TOKEN');
            if (newHash) this.csrfHash = newHash;
            return { ok: r.ok && data.ok, data };
        },
        // ── Date helpers ──
        get todayISO() { return new Date().toISOString().slice(0, 10); },
        relativeDateISO(daysFromToday) {
            const d = new Date();
            d.setDate(d.getDate() + daysFromToday);
            return d.toISOString().slice(0, 10);
        },
        setRelativeDay(d) { this.bookDate = this.relativeDateISO(d); },

        get bookDateLabel() {
            if (!this.bookDate) return '';
            return new Date(this.bookDate + 'T00:00').toLocaleDateString(undefined, { weekday:'short', month:'short', day:'numeric' });
        },
        get bookStaffName() {
            const s = this.staffList.find(x => x.id === this.staffId);
            return s ? s.name : '—';
        },
        get bookEndTime() {
            if (!this.bookSlot) return '';
            const [h, m] = this.bookSlot.split(':').map(Number);
            const t = new Date(); t.setHours(h, m + this.totalDuration, 0, 0);
            return t.toTimeString().slice(0, 5);
        },

        // ── Modal lifecycle ──
        async openBook() {
            if (!this.canBook) { this.flash('Pick a customer, stylist, and at least one service', false); return; }
            this.bookSlot = '';
            this.bookDate = this.todayISO;
            this.bookModal = true;
            await this.loadSlots();
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        async loadSlots() {
            if (!this.staffId) {
                this.slots = [];
                this.bookHoursLabel = '';
                return;
            }
            this.loadingSlots = true;
            this.slots = [];
            try {
                const url = '<?= site_url('admin/pos/availability') ?>'
                    + '?staff_id=' + this.staffId
                    + '&date=' + encodeURIComponent(this.bookDate)
                    + '&duration=' + this.totalDuration;
                const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await r.json();
                if (data.ok) {
                    this.slots = data.slots || [];
                    this.bookHoursLabel = data.workingHours || '';
                    // Auto-select first available slot if none chosen
                    if (!this.bookSlot) {
                        const first = this.slots.find(s => s.available);
                        if (first) this.bookSlot = first.time;
                    } else if (! this.slots.find(s => s.time === this.bookSlot && s.available)) {
                        // Previously selected slot no longer available on this date
                        const first = this.slots.find(s => s.available);
                        this.bookSlot = first ? first.time : '';
                    }
                } else {
                    this.flash(data.msg || 'Could not load slots', false);
                }
            } catch (e) {
                this.flash('Network error loading slots', false);
            }
            this.loadingSlots = false;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        async confirmBook() {
            if (!this.bookSlot) return;
            this.submitting = true;
            const startAt = this.bookDate + ' ' + this.bookSlot + ':00';
            const res = await this.post('<?= site_url('admin/pos/book') ?>', {
                customer_id: this.customerId,
                staff_id: this.staffId,
                service_ids: this.cart.map(i => i.id),
                start_at: startAt,
            });
            this.submitting = false;
            if (res.ok) {
                this.flash(res.data.msg);
                this.bookModal = false;
                setTimeout(() => location.href = res.data.redirect, 700);
            } else {
                this.flash(res.data.msg || 'Booking failed', false);
                // Reload slots in case the conflict happened between picking and confirming
                this.loadSlots();
            }
        },
        async bill(method) {
            if (!this.canBill) { this.flash('Pick a customer first', false); return; }
            const res = await this.post('<?= site_url('admin/pos/bill') ?>', {
                customer_id: this.customerId,
                staff_id: this.staffId || '',
                service_ids: this.cart.map(i => i.id),
                method,
            });
            if (res.ok) {
                this.flash(res.data.msg);
                setTimeout(() => location.href = res.data.redirect, 800);
            } else this.flash(res.data.msg || 'Bill failed', false);
        },
    };
}
</script>
