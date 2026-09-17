<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventories = [

            // =====================================================
            // ROUTER
            // =====================================================

            [
                'inventory_code' => 'INV-NET-001',
                'name' => 'Router MikroTik',
                'category' => 'Router',
                'description' => 'Router MikroTik untuk kebutuhan jaringan kantor.',
                'quantity' => 3,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'available',
                'image' => null,
            ],

            [
                'inventory_code' => 'INV-NET-002',
                'name' => 'Router TP-Link',
                'category' => 'Router',
                'description' => 'Router TP-Link untuk jaringan kantor.',
                'quantity' => 4,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // SWITCH
            // =====================================================

            [
                'inventory_code' => 'INV-NET-003',
                'name' => 'Switch 24 Port',
                'category' => 'Switch',
                'description' => 'Switch jaringan 24 port untuk distribusi koneksi LAN.',
                'quantity' => 5,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            [
                'inventory_code' => 'INV-NET-004',
                'name' => 'Switch 16 Port',
                'category' => 'Switch',
                'description' => 'Switch jaringan 16 port.',
                'quantity' => 3,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            [
                'inventory_code' => 'INV-NET-005',
                'name' => 'Switch 8 Port',
                'category' => 'Switch',
                'description' => 'Switch jaringan 8 port untuk kebutuhan jaringan kecil.',
                'quantity' => 6,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // ACCESS POINT
            // =====================================================

            [
                'inventory_code' => 'INV-NET-006',
                'name' => 'Access Point TP-Link',
                'category' => 'Access Point',
                'description' => 'Access point untuk jaringan WiFi kantor.',
                'quantity' => 8,
                'condition' => 'good',
                'location' => 'Lantai 1',
                'status' => 'in_use',
                'image' => null,
            ],

            [
                'inventory_code' => 'INV-NET-007',
                'name' => 'Access Point Ubiquiti',
                'category' => 'Access Point',
                'description' => 'Access point Ubiquiti untuk jaringan WiFi.',
                'quantity' => 5,
                'condition' => 'good',
                'location' => 'Lantai 2',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // MODEM
            // =====================================================

            [
                'inventory_code' => 'INV-NET-008',
                'name' => 'Modem Internet',
                'category' => 'Modem',
                'description' => 'Modem koneksi internet kantor.',
                'quantity' => 2,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // FIREWALL
            // =====================================================

            [
                'inventory_code' => 'INV-NET-009',
                'name' => 'Firewall Network Appliance',
                'category' => 'Firewall',
                'description' => 'Perangkat firewall untuk keamanan jaringan.',
                'quantity' => 1,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // SERVER
            // =====================================================

            [
                'inventory_code' => 'INV-NET-010',
                'name' => 'Network Server',
                'category' => 'Server',
                'description' => 'Server untuk kebutuhan layanan jaringan internal.',
                'quantity' => 2,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // NETWORK CARD
            // =====================================================

            [
                'inventory_code' => 'INV-NET-011',
                'name' => 'LAN Card',
                'category' => 'Network Card',
                'description' => 'Kartu jaringan LAN untuk komputer.',
                'quantity' => 10,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // KABEL LAN
            // =====================================================

            [
                'inventory_code' => 'INV-NET-012',
                'name' => 'Kabel LAN Cat6',
                'category' => 'Kabel Jaringan',
                'description' => 'Kabel jaringan Cat6 untuk koneksi LAN.',
                'quantity' => 50,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            [
                'inventory_code' => 'INV-NET-013',
                'name' => 'Kabel LAN Cat5e',
                'category' => 'Kabel Jaringan',
                'description' => 'Kabel jaringan Cat5e untuk kebutuhan LAN.',
                'quantity' => 40,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // CONNECTOR
            // =====================================================

            [
                'inventory_code' => 'INV-NET-014',
                'name' => 'RJ45 Connector',
                'category' => 'Connector',
                'description' => 'Konektor RJ45 untuk kabel jaringan.',
                'quantity' => 100,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // PATCH PANEL
            // =====================================================

            [
                'inventory_code' => 'INV-NET-015',
                'name' => 'Patch Panel 24 Port',
                'category' => 'Patch Panel',
                'description' => 'Patch panel 24 port untuk manajemen kabel jaringan.',
                'quantity' => 3,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // RACK
            // =====================================================

            [
                'inventory_code' => 'INV-NET-016',
                'name' => 'Network Rack 20U',
                'category' => 'Network Rack',
                'description' => 'Rack untuk menyimpan perangkat jaringan.',
                'quantity' => 2,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // UPS
            // =====================================================

            [
                'inventory_code' => 'INV-NET-017',
                'name' => 'UPS Network',
                'category' => 'UPS',
                'description' => 'UPS untuk menjaga perangkat jaringan tetap menyala saat listrik padam.',
                'quantity' => 4,
                'condition' => 'good',
                'location' => 'Server Room',
                'status' => 'in_use',
                'image' => null,
            ],

            // =====================================================
            // LAN TESTER
            // =====================================================

            [
                'inventory_code' => 'INV-NET-018',
                'name' => 'LAN Cable Tester',
                'category' => 'Network Tools',
                'description' => 'Alat untuk melakukan pengecekan kabel jaringan.',
                'quantity' => 3,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // CRIMPING TOOL
            // =====================================================

            [
                'inventory_code' => 'INV-NET-019',
                'name' => 'Crimping Tool RJ45',
                'category' => 'Network Tools',
                'description' => 'Alat untuk memasang connector RJ45 pada kabel LAN.',
                'quantity' => 4,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],

            // =====================================================
            // TANGGA / PERALATAN INSTALASI
            // =====================================================

            [
                'inventory_code' => 'INV-NET-020',
                'name' => 'Tangga Instalasi Jaringan',
                'category' => 'Network Tools',
                'description' => 'Tangga untuk membantu proses instalasi perangkat jaringan.',
                'quantity' => 2,
                'condition' => 'good',
                'location' => 'IT Room',
                'status' => 'available',
                'image' => null,
            ],
        ];

        foreach ($inventories as $inventory) {
            Inventory::updateOrCreate(
                [
                    'inventory_code' => $inventory['inventory_code'],
                ],
                $inventory
            );
        }
    }
}