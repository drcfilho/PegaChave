# 📖 Guia do Usuário e Administrador

Este guia explica como operar o sistema no dia a dia.

## 🔑 O Quiosque da Portaria

O Quiosque é a tela principal (`/`) que fica aberta no tablet, computador ou totem na portaria da instituição.

### Como realizar Empréstimos e Devoluções
1. O Quiosque detecta automaticamente a câmera do dispositivo. Aponte o crachá (QR Code) de um Usuário cadastrado para a câmera.
2. A câmera fará a leitura e salvará o Usuário na sessão por alguns segundos.
3. Em seguida, aponte a etiqueta (QR Code) da Chave que ele deseja retirar.
4. O sistema fará um "Beep" duplo de sucesso e registrará a saída no banco de dados, vinculando a chave àquele usuário.
5. Para a **devolução**, basta apontar apenas o QR Code da Chave. O sistema entende sozinho que ela estava em uso e a devolve automaticamente.

### Modo Offline
Se a internet cair, o quiosque guardará as leituras localmente no navegador (IndexedDB). Assim que a conexão for restabelecida, o sistema enviará todos os dados em lote de forma transparente para o servidor. Um ícone de nuvem cortada avisa quando o sistema está offline.

---

## 🛠️ O Painel de Administração

Acessado através de `/admin`.

### Cadastros Básicos (Salas e Usuários)
Você precisa cadastrar Chaves e Usuários para que o quiosque funcione.
No menu lateral esquerdo, vá em **Chaves e Salas** ou **Usuários** e clique no botão de Adicionar (+). O sistema aceita qualquer formato nos campos e automaticamente gera o Hash MD5 por trás dos panos para criar o código QR Code único.

### Gerando as Etiquetas
Vá na aba **Gerador de QR Code**.
* Pesquise e marque o checkbox ao lado das chaves ou usuários que você deseja gerar etiqueta.
* Clique em "Gerar Códigos".
* O sistema cria imagens em alta resolução com o logo e nome da sua instituição prontos para baixar (PNG) e mandar para a gráfica ou imprimir na impressora de etiquetas.

### Controle de Limites e Acesso Restrito
Você pode garantir que um aluno só pegue 1 chave, ou um professor pegue 3 chaves.
* Vá em **Configurações** e altere o `Limite Máximo de Chaves`. (Colocar 0 significa ilimitado).
* **Restrições de Perfis:** Na aba `Restrições`, você pode criar regras. Exemplo: "Perfil ALUNO não pode pegar a chave Laboratório de Química". O quiosque bloqueará a tentativa de retirada na hora.
* **Matrículas Permitidas:** Na edição de uma Chave Específica (ex: CPD ou Sala do Servidor), você pode ditar quais são as Matrículas exatas das pessoas autorizadas. Quem não estiver na lista toma "Acesso Negado".

### Reservas e Agendamentos
Na aba **Reservas de Chaves**, o gestor pode selecionar uma chave, um usuário, uma data e os horários de início e fim.
Durante aquele intervalo programado, a chave só poderá ser retirada no Quiosque por aquela pessoa específica. Ninguém mais.

### Arquivamento (Soft Delete)
Ao tentar excluir um Usuário ou uma Chave que possua histórico no sistema, o PegaChave fará o **Arquivamento Seguro** no lugar da deleção permanente. Isso evita que os relatórios antigos quebrem.
Itens arquivados podem ser visualizados nas abas `Arquivados`, mas exigem a digitação da senha do Administrador para exibir os dados (Auditoria Protegida).
