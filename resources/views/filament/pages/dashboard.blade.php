<x-filament::page>
    <x-filament::grid>
        {{ $this->getHeaderWidgets()[0] }}
    </x-filament::grid>

    <x-filament::grid class="mt-8">
        <x-filament::grid.column>
            <x-filament::card>
                <h2 class="text-lg font-medium">Menu Cepat</h2>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <x-filament::button
                        color="success"
                        icon="heroicon-o-shopping-cart"
                        href="{{ route('filament.admin.resources.sales.create') }}"
                        class="w-full justify-start"
                    >
                        Tambah Penjualan
                    </x-filament::button>

                    <x-filament::button
                        color="danger"
                        icon="heroicon-o-receipt-refund"
                        href="{{ route('filament.admin.resources.expenses.create') }}"
                        class="w-full justify-start"
                    >
                        Catat Pengeluaran
                    </x-filament::button>

                    <x-filament::button
                        color="warning"
                        icon="heroicon-o-credit-card"
                        href="{{ route('filament.admin.resources.debts.create') }}"
                        class="w-full justify-start"
                    >
                        Catat Hutang
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-banknotes"
                        href="{{ route('filament.admin.resources.payments.create') }}"
                        class="w-full justify-start"
                    >
                        Terima Pembayaran
                    </x-filament::button>
                </div>
            </x-filament::card>
        </x-filament::grid.column>
    </x-filament::grid>
</x-filament::page>
