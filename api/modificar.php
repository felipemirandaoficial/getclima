<?php
// URL do arquivo clima.html
$arquivoUrl = 'https://api.simasul.com.br/status/clima.html'; // Altere para o URL correto do arquivo clima.html
$linkFileUrl = 'https://api.simasul.com.br/status/tipo.chuva.txt'; // URL para o arquivo tipo.chuva.txt
$linkStatustempoUrl = 'https://www.theweather.com/getwid/e47390d95033e30de9d080fd3bea3591'; // Link do status do tempo

// Lê o arquivo tipo.chuva.txt diretamente da URL
if ($stream = fopen($linkFileUrl, 'r')) {
    $link = stream_get_contents($stream, -1, 0);
    fclose($stream);
}

// Lê o arquivo clima.html diretamente da URL
$html = file_get_contents($arquivoUrl);

if ($html !== false) {
    // Lê o conteúdo do link de status do tempo
    $link_statustempo = file_get_contents($linkStatustempoUrl);
    
    // Faz a correspondência do texto alternativo da imagem
    preg_match('/<img[^>]*alt=["\']([^"\']*)["\'][^>]*>/', $link_statustempo, $matches);
    if (isset($matches[1])) {
        $altText = $matches[1];
    } else {
        $altText = "Geralmente Limpo";
    }

    // Remove o link <a> específico com href="https://app.weathercloud.net/d0710389996"
    $html = preg_replace('/<a href="https:\/\/app\.weathercloud\.net\/d0710389996"[^>]*>.*?<\/a>/', '', $html);
    
    // Substitui o atributo alt da primeira imagem encontrada
    $html = preg_replace('/<img([^>]*)alt=["\'][^"\']*["\']([^>]*)>/', '<img$1alt="' . $altText . '" title="' . $altText . '"$2>', $html, 1);

    // Remove todo o bloco <svg> até </svg>
    $html = preg_replace('/<svg[^>]*>.*?<\/svg>/s', '', $html);

    // Define a nova linha para a temperatura atual
    $novaLinha = "<tr class='hijo-all'> 
                    <td class='blanco'>&nbsp;</td> 
                    <td class='nomDay'>Agora</td> 
                    <td class='temps'> 
                        <span  style='color:red' id='temp_cur2' title='Sensação Termica atual em Graus Celsius' class='TgMin'>0&deg;</span>
                    </td> 
                    <td class='simb'> 
                        <img src='$link' width='26' title='$altText'> 
                    </td> 
                    <td class='blanco'>&nbsp;</td> 
                  </tr><tr><td style='font-size: 10px; text-align:center'>$altText</td></tr>";

    // Insere a nova linha no HTML
    $html = preg_replace('/(<\/tr>\s*<\/table>)/', $novaLinha . '$1', $html);

    // Retorna o HTML modificado
    echo $html;
} else {
    echo "Arquivo não encontrado.";
}
?>
<script src="https://getclima.vercel.app/clima.js"></script>
