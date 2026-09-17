<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppGateway;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppGatewayController extends Controller
{
    /**
     * Provider WhatsApp yang didukung.
     */
    private array $supportedProviders = [
        'waha',
        'fonnte',
        'wablas',
        'custom',
    ];

    /**
     * Ambil pengaturan WhatsApp Gateway.
     */
    public function index(): JsonResponse
    {
        $gateway = WhatsAppGateway::query()
            ->latest('id')
            ->first();

        if (!$gateway) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan WhatsApp Gateway belum dibuat.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan WhatsApp Gateway berhasil diambil.',
            'data' => [
                'id' => $gateway->id,
                'name' => $gateway->name,
                'provider' => $gateway->provider,
                'url' => $gateway->url,
                'api_id' => $gateway->api_id,
                'api_key' => '********',
                'is_active' => $gateway->is_active,
            ],
        ]);
    }

    /**
     * Simpan pengaturan WhatsApp Gateway.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'provider' => [
                'required',
                'string',
                'in:' . implode(',', $this->supportedProviders),
            ],

            'url' => [
                'required',
                'url',
                'max:500',
            ],

            'api_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'api_key' => [
                'required',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengaturan WhatsApp Gateway tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Jika gateway baru langsung diaktifkan,
         * nonaktifkan gateway lainnya.
         */
        if ($request->boolean('is_active', true)) {
            WhatsAppGateway::query()->update([
                'is_active' => false,
            ]);
        }

        $gateway = WhatsAppGateway::create([
            'name' => $request->name,
            'provider' => strtolower($request->provider),
            'url' => $request->url,
            'api_id' => $request->api_id,
            'api_key' => $request->api_key,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan WhatsApp Gateway berhasil disimpan.',
            'data' => [
                'id' => $gateway->id,
                'name' => $gateway->name,
                'provider' => $gateway->provider,
                'url' => $gateway->url,
                'api_id' => $gateway->api_id,
                'api_key' => '********',
                'is_active' => $gateway->is_active,
            ],
        ], 201);
    }

    /**
     * Perbarui pengaturan WhatsApp Gateway.
     */
    public function update(
        Request $request,
        WhatsAppGateway $whatsappGateway
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'provider' => [
                'required',
                'string',
                'in:' . implode(',', $this->supportedProviders),
            ],

            'url' => [
                'required',
                'url',
                'max:500',
            ],

            'api_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'api_key' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengaturan WhatsApp Gateway tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Jika gateway ini diaktifkan,
         * nonaktifkan gateway lainnya.
         */
        if ($request->boolean('is_active', false)) {
            WhatsAppGateway::query()
                ->where('id', '!=', $whatsappGateway->id)
                ->update([
                    'is_active' => false,
                ]);
        }

        $data = [
            'name' => $request->name,
            'provider' => strtolower($request->provider),
            'url' => $request->url,
            'api_id' => $request->api_id,
            'is_active' => $request->boolean('is_active', false),
        ];

        /*
         * API Key hanya diganti kalau user memasukkan
         * API Key baru.
         */
        if ($request->filled('api_key')) {
            $data['api_key'] = $request->api_key;
        }

        $whatsappGateway->update($data);

        $whatsappGateway->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan WhatsApp Gateway berhasil diperbarui.',
            'data' => [
                'id' => $whatsappGateway->id,
                'name' => $whatsappGateway->name,
                'provider' => $whatsappGateway->provider,
                'url' => $whatsappGateway->url,
                'api_id' => $whatsappGateway->api_id,
                'api_key' => '********',
                'is_active' => $whatsappGateway->is_active,
            ],
        ]);
    }

    /**
     * Aktif/nonaktifkan gateway.
     */
    public function toggleStatus(
        WhatsAppGateway $whatsappGateway
    ): JsonResponse {
        $newStatus = !$whatsappGateway->is_active;

        /*
         * Hanya boleh ada satu gateway aktif.
         */
        if ($newStatus) {
            WhatsAppGateway::query()
                ->where('id', '!=', $whatsappGateway->id)
                ->update([
                    'is_active' => false,
                ]);
        }

        $whatsappGateway->update([
            'is_active' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => $newStatus
                ? 'WhatsApp Gateway berhasil diaktifkan.'
                : 'WhatsApp Gateway berhasil dinonaktifkan.',
            'data' => [
                'id' => $whatsappGateway->id,
                'provider' => $whatsappGateway->provider,
                'is_active' => $whatsappGateway->is_active,
            ],
        ]);
    }

    /**
     * Hapus konfigurasi gateway.
     */
    public function destroy(
        WhatsAppGateway $whatsappGateway
    ): JsonResponse {
        $wasActive = $whatsappGateway->is_active;

        $whatsappGateway->delete();

        /*
         * Kalau gateway yang dihapus adalah gateway aktif,
         * jangan otomatis mengaktifkan gateway lain.
         *
         * Admin bisa memilih gateway berikutnya secara manual.
         */
        return response()->json([
            'success' => true,
            'message' => 'Pengaturan WhatsApp Gateway berhasil dihapus.',
            'data' => [
                'deleted_id' => $whatsappGateway->id,
                'was_active' => $wasActive,
            ],
        ]);
    }

    /**
     * Test konfigurasi gateway.
     */
    public function test(
        WhatsAppGateway $whatsappGateway,
        WhatsAppService $whatsappService
    ): JsonResponse {
        $result = $whatsappService->testGateway($whatsappGateway);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'WhatsApp Gateway berhasil diuji.'
                : 'WhatsApp Gateway gagal diuji.',
            'data' => [
                'gateway' => $result['gateway'] ?? [
                    'id' => $whatsappGateway->id,
                    'name' => $whatsappGateway->name,
                    'provider' => $whatsappGateway->provider,
                ],
                'status' => $result['status'] ?? null,
                'response' => $result['response'] ?? null,
            ],
        ], $result['success'] ? 200 : ($result['status'] >= 400 ? $result['status'] : 500));
    }

    /**
     * Test kirim pesan WhatsApp.
     */
    public function testSend(
        Request $request,
        WhatsAppGateway $whatsappGateway,
        WhatsAppService $whatsappService
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data test WhatsApp tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Hanya gunakan gateway yang diminta.
         * Sementara kita aktifkan secara logika melalui
         * pengiriman langsung berdasarkan gateway.
         */
        $result = $whatsappService->sendUsingGateway(
            $whatsappGateway,
            $request->phone,
            $request->message
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Pesan WhatsApp berhasil dikirim.'
                : 'Pesan WhatsApp gagal dikirim.',
            'data' => [
                'gateway' => $result['gateway'] ?? null,
                'status' => $result['status'] ?? null,
                'response' => $result['response'] ?? null,
            ],
        ], $result['success'] ? 200 : ($result['status'] >= 400 ? $result['status'] : 500));
    }
}

