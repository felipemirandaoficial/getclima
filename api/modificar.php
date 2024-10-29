<?php
// URL do arquivo clima.html
$arquivoUrl = 'https://api.simasul.com.br/status/clima.html';
$linkFileUrl = 'https://api.simasul.com.br/status/tipo.chuva.txt';
$linkStatustempoUrl = 'https://www.theweather.com/getwid/e47390d95033e30de9d080fd3bea3591';

// Lê o arquivo tipo.chuva.txt diretamente da URL
if ($stream = fopen($linkFileUrl, 'r')) {
    $link = stream_get_contents($stream, -1, 0);
    fclose($stream);
}

// Lê o arquivo clima.html diretamente da URL
$html = file_get_contents($arquivoUrl);

if ($html !== false) {
    $link_statustempo = file_get_contents($linkStatustempoUrl);
    preg_match('/<img[^>]*alt=["\']([^"\']*)["\'][^>]*>/', $link_statustempo, $matches);
    $altText = isset($matches[1]) ? $matches[1] : "Geralmente Limpo";

    $html = preg_replace('/<a href="https:\/\/app\.weathercloud\.net\/d0710389996"[^>]*>.*?<\/a>/', '', $html);
    $html = preg_replace('/<img([^>]*)alt=["\'][^"\']*["\']([^>]*)>/', '<img$1alt="' . $altText . '" title="' . $altText . '"$2>', $html, 1);
    $html = preg_replace('/<svg[^>]*>.*?<\/svg>/s', '', $html);

    $novaLinha = "<tr class='hijo-all'> 
                    <td class='blanco'>&nbsp;</td> 
                    <td class='nomDay'>Agora</td> 
                    <td class='temps'> 
                        <span style='color:red' id='temp_cur2' title='Sensação Térmica atual em Graus Celsius' class='TgMin'>0&deg;</span>
                    </td> 
                    <td class='simb'> 
                        <img src='$link' width='26' title='$altText'> 
                    </td> 
                    <td class='blanco'>&nbsp;</td> 
                  </tr><tr><td style='font-size: 10px; text-align:center'>$altText</td></tr>";

    $html = preg_replace('/(<\/tr>\s*<\/table>)/', $novaLinha . '$1', $html);
    echo $html;
} else {
    echo "Arquivo não encontrado.";
}
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    buscarTemperatura(); // Chama a função para buscar a temperatura atual
});

function buscarTemperatura(url = 'https://www.wunderground.com/dashboard/pws/IAQUID2') {
    // URL alternativa caso a primeira falhe
    var urlAlternativa = 'https://www.wunderground.com/weather/SBCG';

    // Requisição HTTP para obter o conteúdo do site
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = xhr.responseText;
            var temperaturaFahrenheit, temperaturaSensa;

            if (response) {
                // Tentar extrair a temperatura usando o primeiro formato
                temperaturaFahrenheit = buscarTexto(response, 'font-size:100%;margin:0;"> ', '° </div>');
                temperaturaSensa = buscarTexto(response, 'Feels Like', 'span>&nbsp;');
                temperaturaSensa = buscarTexto(temperaturaSensa, '"color:;">', '</');

                if (!temperaturaSensa && url !== urlAlternativa) {
                    console.log("Não foi possível obter a temperatura, tentando a URL alternativa.");
                    downloadPaginaAlternativa(urlAlternativa);
                    return; // Para parar a execução desta função ao tentar a URL alternativa
                }
                // Exibir as temperaturas ou mostrar "Offline"
                if (temperaturaFahrenheit) {
                    var temperaturaCelsius = (parseFloat(temperaturaFahrenheit) - 32) * 5 / 9;
                } else {
                    console.log("Temperatura Fahrenheit não encontrada.");
                }

                if (temperaturaSensa && temperaturaFahrenheit) {
                    var temperaturaCelsius2 = (parseFloat(temperaturaSensa) - 32) * 5 / 9;
                    temperaturaCelsius2 = (temperaturaCelsius + temperaturaCelsius2) / 2;
                    document.getElementById('temp_cur2').textContent = temperaturaCelsius2.toFixed(0) + '°';
                } else {
                    document.getElementById('temp_cur2').textContent = '666';
                }
            } else {
                console.log("Resposta vazia, tentando a URL alternativa.");
                buscarTemperatura(urlAlternativa);
            }
        } else {
            console.log("Erro na primeira URL, tentando a URL alternativa.");
            buscarTemperatura(urlAlternativa);
        }
    };

    xhr.onerror = function() {
        console.log("Erro de conexão. Tentando a URL alternativa.");
        buscarTemperatura(urlAlternativa);
    };

    xhr.send();
}

// Função para baixar a página alternativa usando PHP
function downloadPaginaAlternativa(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'https://api.simasul.com.br/info/baixar.php?url=' + encodeURIComponent(url), true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = xhr.responseText;

            // Extraia os dados necessários a partir do HTML da página baixada
            var temperaturaFahrenheit = buscarTexto(response, 'class="wu-value wu-value-to"', '</span>');
            temperaturaFahrenheit = temperaturaFahrenheit.replace('style="color:;">', '').trim(); // Remove a parte indesejada

            // Para extrair o "Feels Like"
            var temperaturaSensa = buscarTexto(response, 'class="temp"', '°');
            temperaturaSensa = temperaturaSensa.replace(/style="color:[^;]*;">/, '').trim(); // Remove a parte indesejada
            
            // Calcular e exibir a temperatura
            console.log(temperaturaFahrenheit + '+' + temperaturaSensa);
            
            if (temperaturaSensa) {
                var temperaturaCelsius2 = (parseFloat(temperaturaSensa) - 32) * 5 / 9;
                document.getElementById('temp_cur2').textContent = temperaturaCelsius2.toFixed(0) + '°';
            } else {
                document.getElementById('temp_cur2').textContent = 'Offline';
            }
        } else {
            console.log("Erro ao baixar a página alternativa.");
        }
    };

    xhr.onerror = function() {
        console.log("Erro na requisição para a URL alternativa.");
    };

    xhr.send();
}

// Função para buscar um trecho de texto entre duas strings
function buscarTexto(texto, inicio, fim) {
    if (!texto) return null;

    var inicioIndex = texto.indexOf(inicio);
    if (inicioIndex === -1) return null;

    var fimIndex = texto.indexOf(fim, inicioIndex + inicio.length);
    if (fimIndex === -1) return null;

    return texto.substring(inicioIndex + inicio.length, fimIndex);
}
</script>

