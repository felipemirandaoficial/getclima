<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    
    // Configurações do contexto para simular um navegador
    $context = stream_context_create(array(
        'http' => array(
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3\r\n"
        )
    ));

    // Baixar a página
    $html = @file_get_contents($url, false, $context);

    // Verificar se o download foi bem-sucedido
    if ($html === false) {
        echo "Erro ao baixar a página.";
    } else {
        // Retornar o conteúdo HTML
        echo $html;
    }
} else {
    echo "URL não fornecida.";
}
