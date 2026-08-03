<?php
/**
 * Pix (Mercado Pago) Payment Adapter for FossBilling
 * 
 * @author Khallen
 * @version 1.0.0
 */

class Payment_Adapter_Pix
{
    private $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public static function getConfig()
    {
        return [
            'supports_one_time_payments' => true,
            'supports_subscriptions'     => false,
            'description'                => 'Pagamentos via Pix utilizando a API do Mercado Pago. Desenvolvido por Khallen.',
            'logo' => [
                'logo' => 'https://logospng.org/download/pix/logo-pix-icone-1024.png',
                'class' => 'custom-logo'
            ],
            'form' => [
                'access_token' => [
                    'text', [
                        'label' => 'Access Token (Mercado Pago)',
                        'description' => 'Credencial de Produção (Access Token) do Mercado Pago.',
                        'required' => true,
                    ],
                ],
            ],
        ];
    }

    public function getHtml($api_admin, $invoice_id, $subscription)
    {
        $invoice = $api_admin->invoice_get(['id' => $invoice_id]);
        
        $payload = [
            "transaction_amount" => (float) $invoice['total'],
            "description" => 'Fatura #' . $invoice['serie_nr'],
            "payment_method_id" => "pix",
            "payer" => [
                "email" => $invoice['buyer']['email'],
                "first_name" => $invoice['buyer']['first_name'],
                "last_name" => $invoice['buyer']['last_name']
            ]
        ];

        $ch = curl_init("https://api.mercadopago.com/v1/payments");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->config['access_token'],
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || !$response) {
            return '<div class="alert alert-danger">Serviço indisponível no momento. Tente novamente.</div>';
        }

        $result = json_decode($response, true);

        if (!empty($result['point_of_interaction']['transaction_data'])) {
            $qr_base64 = $result['point_of_interaction']['transaction_data']['qr_code_base64'];
            $pix_code = $result['point_of_interaction']['transaction_data']['qr_code'];

            return sprintf('
                <div class="pix-payment-box" style="text-align:center; padding:20px; background:#fff; border:1px solid #e1e1e1; border-radius:8px;">
                    <h3 style="color:#32bcad; font-weight:bold;">
                        <img src="https://logospng.org/download/pix/logo-pix-icone-1024.png" width="20" style="vertical-align:middle; margin-right:5px;">Pix
                    </h3>
                    <p style="color:#666; font-size:14px;">Escaneie o QR Code pelo app do seu banco:</p>
                    <img src="data:image/jpeg;base64,%s" style="width:200px; height:200px; margin: 10px 0; border:1px solid #eee; border-radius:5px;" />
                    
                    <div style="margin-top:15px;">
                        <p style="color:#666; font-size:14px; margin-bottom:5px;">Ou copie o código abaixo:</p>
                        <textarea id="pixCode" readonly style="width:100%%; height:70px; padding:10px; font-size:12px; border:1px solid #ccc; border-radius:4px; resize:none;">%s</textarea>
                        <button onclick="copyPixCode()" style="margin-top:10px; padding:8px 15px; background:#32bcad; color:#fff; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Copiar Código Pix</button>
                    </div>

                    <script>
                    function copyPixCode() {
                        const code = document.getElementById("pixCode");
                        code.select();
                        document.execCommand("copy");
                        alert("Código Pix copiado!");
                    }
                    </script>
                </div>
            ', $qr_base64, $pix_code);
        }

        return '<div class="alert alert-warning">Não foi possível gerar o Pix. Verifique se os dados da fatura estão corretos.</div>';
    }

    public function processTransaction($api_admin, $id, $data, $gateway_id)
    {
        // MP envia o ID do pagamento via GET ou POST no webhook
        $payment_id = $data['get']['id'] ?? $data['post']['data']['id'] ?? null;

        if (!$payment_id) {
            throw new Exception('ID do pagamento ausente.');
        }

        $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->config['access_token']
            ],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $payment_info = json_decode($response, true);

        if (isset($payment_info['status']) && $payment_info['status'] === 'approved') {
            $tx = $api_admin->invoice_transaction_get(['id' => $id]);
            
            $api_admin->invoice_transaction_update([
                'id' => $id,
                'status' => 'processed',
            ]);

            $api_admin->invoice_mark_as_paid([
                'id' => $tx['invoice_id'],
                'execute' => true
            ]);
        }
    }
}

