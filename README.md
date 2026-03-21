Alterações
 #AgMelhorEnvioShippingTracker
    - adicionado um if, tratando de um elemento individual, esse if fez com que pareça de aparecer erro.

 #AdminAgMelhorEnvioLabelsTrackController
    - inserido dentro das actions o trackings
    - criada a função displayTrackingsLink
        - dentro dessa função será enviado um request para a api do melhorenvio, depois é jogado para dentro de um foreach para atualizar os dados e por último verifica se tem algum codigo de tracking, caso exista o botão para rastrear será criado com o link e o código de rastreamento.