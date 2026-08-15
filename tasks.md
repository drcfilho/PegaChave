# Planejamento de Melhorias e Novas Funcionalidades (PegaChave)

## ⏳ Backlog Atual

### 1. Configuração de Alertas e Atrasos (Notificações)
- [ ] Adicionar na tabela `configuracoes` o limite de tempo em horas para considerar uma chave como "Atrasada" (ex: 4 horas).
- [ ] Criar interface na tela de Configurações para o Admin alterar esse limite.
- [ ] Criar rotina (Cron Job ou Controller de background) que varre movimentações ativas que excederam o limite.
- [ ] Disparar e-mail de alerta para o e-mail cadastrado do usuário (ou para o Admin) informando sobre o atraso.
- [ ] Destacar chaves atrasadas visualmente no Dashboard.

### 2. Módulo de Impressão Térmica (Zebra/Argox)
- [ ] Criar layout de HTML e CSS com medidas exatas para bobinas térmicas contínuas.
- [ ] Adicionar botão "Imprimir em Etiqueta Térmica" na tela de QR Codes.
- [ ] Garantir ausência de margens do navegador e dimensionamento de código de barras 2D.

### 3. API Externa e Webhooks (Integração WhatsApp / ERP)
- [ ] Desenvolver sistema de Geração de Tokens de API (Bearer Token) no painel administrativo.
- [ ] Criar endpoints `GET /api/public/chaves` protegidos pelo token.
- [ ] Adicionar suporte a Webhooks: No evento de devolução com avaria ou atraso detectado, disparar um payload POST para uma URL configurada pelo cliente (para integração com Chatbots de WhatsApp).

## ✅ Concluídos
- Refatoração RESTful e Middlewares
- Injeção de Dependências
- Migração para BladeOne e Tailwind
- Múltiplas Portarias (Multitenancy)
- Sistema de Ocorrências na devolução
