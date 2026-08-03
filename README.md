# Módulo Pix (Mercado Pago) para FossBilling

Um plugin nativo e leve para o [FossBilling](https://fossbilling.org/) que adiciona o método de pagamento Pix, utilizando a API oficial do Mercado Pago para geração de QR Code e função "Copia e Cola".

**Autor:** Khallen  
**Versão:** 1.0.0  
**Compatibilidade:** FossBilling v0.x e superiores.

---

## 🚀 Funcionalidades

- Gera QR Code instantâneo na tela da fatura.
- Campo de "Pix Copia e Cola" com botão simplificado de copiar.
- Baixa automática da fatura no FossBilling após o pagamento (via Webhook/IPN).
- Interface limpa e responsiva.

---

## 📦 Como Instalar no FossBilling

1. Faça o download do arquivo `Pix.php` deste repositório.
2. Acesse os arquivos do seu servidor (via FTP, cPanel, ou SSH).
3. Navegue até o diretório do seu FossBilling e vá para o caminho:
   `library/Payment/Adapter/`
4. Faça o upload do arquivo `Pix.php` dentro dessa pasta.
5. Acesse o Painel de Administração do seu FossBilling.
6. Vá em **Configurações** (engrenagem) -> **Gateways de Pagamento**.
7. Na aba **Novo gateway de pagamento**, encontre o "Pix" na lista e clique no ícone para ativá-lo.
8. Vá para a aba **Configurados**, edite o gateway Pix inserindo o seu `Access Token` do Mercado Pago (veja abaixo como obter) e marque a opção **Ativado**.

---

## 🔑 Como Obter o Access Token (Mercado Pago)

Para que o plugin consiga gerar os pagamentos, ele precisa se comunicar com a sua conta do Mercado Pago.

1. Acesse o painel de desenvolvedores do Mercado Pago: [https://www.mercadopago.com.br/developers/panel/applications](https://www.mercadopago.com.br/developers/panel/applications)
2. Faça login com a sua conta.
3. Clique em **Criar aplicação** (Dê um nome, ex: *Meu Painel FossBilling*, selecione "Pagamentos Online" e marque a opção de não estar usando uma plataforma de e-commerce específica).
4. Após criar, entre na aplicação e no menu lateral esquerdo, vá em **Credenciais de Produção**.
5. Copie o código longo chamado **Access Token** (ele geralmente começa com `APP_USR-`).
6. Cole este código lá no painel do FossBilling nas configurações do plugin Pix que você ativou.

---

## 🔄 Configurando o Webhook (Baixa Automática)

Para que o FossBilling marque a fatura como "Paga" automaticamente assim que o cliente fizer o Pix, o Mercado Pago precisa enviar uma notificação para o seu sistema.

1. No painel de desenvolvedores do Mercado Pago (dentro da aplicação que você criou), vá em **Webhooks** ou **Notificações IPN**.
2. Adicione a URL de notificação do seu FossBilling. Ela segue este formato padrão:
   `https://seu-dominio.com.br/api/guest/invoice/transaction_process?gateway_id=X`
   *(Substitua `seu-dominio.com.br` pelo seu site e o `X` pelo ID do gateway Pix no FossBilling. Você pode ver o ID do gateway acessando o painel admin do FossBilling e olhando o final da URL ao editar o gateway Pix).*
3. Em **Eventos**, marque a opção **Pagamentos** (Payments).
4. Salve. 

Pronto! Agora seu FossBilling já está apto a receber via Pix com baixa automática.

---

## 👨‍💻 Contribuição

Sinta-se à vontade para abrir Issues ou enviar Pull Requests caso encontre algum bug ou tenha sugestões de melhorias!
