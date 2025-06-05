<x-app-layout>

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 m-4">
        <div class="flex">
            {{-- <Button as-chil size="sm" class="bg-blue-500">
                    <Link :href="route('facturacion.create')" class="flex">
                    <CirclePlus class="mx-1" /> Crear Factura
                    </Link>
                </Button> --}}
        </div>

        <div class="flex h-full flex-1 flex-col  rounded-xl m-4">
            <x-bladewind::table>
                <x-slot name="header">
                    <th>Número de Factura</th>
                    <th>Cliente</th>
                    <th>Forma de Pago</th>
                    <th>Correo</th>
                    <th>Identificación</th>
                    <th>Monto</th>
                    <th>Acciones</th>
                </x-slot>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice['number'] }}</td>
                        <td>{{ $invoice['names'] }}</td>
                        <td>{{ $invoice['payment_form']['name'] }}</td>
                        <td>{{ $invoice['email'] }}</td>
                        <td>{{ $invoice['identification'] }}</td>
                        <td>{{ $invoice['total'] }}</td>
                        <td>
                            <x-bladewind::button size="tiny" icon="pencil">
                                Editar
                            </x-bladewind::button>
                            <x-bladewind::button color="red" size="tiny" icon="trash">
                                Eliminar
                            </x-bladewind::button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>No hay registros disponibles</td>
                    </tr>
                @endforelse

            </x-bladewind::table>
        </div>
    </div>

</x-app-layout>
