<script>
document.addEventListener("DOMContentLoaded", function() {
    // Chama a função BuscarTemperatura e verifica o retorno
    var temperatura = BuscarTemperatura('https://www.wunderground.com/dashboard/pws/IAQUID2');
    if (!temperatura || temperatura === 0) {
        // Se não houver temperatura, tenta a URL alternativa
        downloadPaginaAlternativa('https://www.wunderground.com/weather/SBCG');
    }
});

// Função para buscar a temperatura
function BuscarTemperatura(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, false); // Modo síncrono para garantir retorno imediato
    var temperaturaFahrenheit = null;
    var temperaturaSensa = null;

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = xhr.responseText;
            
            if (response) {
                if (url.includes('IAQUID2')) {
                    // Extrair a temperatura e a sensação térmica da primeira URL
                    temperaturaFahrenheit = buscarTexto(response, 'font-size:100%;margin:0;"> ', '° </div>');
                    temperaturaSensa = buscarTexto(response, 'Feels Like', 'span>&nbsp;');
                    temperaturaSensa = buscarTexto(temperaturaSensa, '"color:;">', '</');
                }
            }
        } else {
            console.log("Erro ao acessar a URL: " + url);
        }
    };

    xhr.onerror = function() {
        console.log("Erro ao acessar a URL:", url);
    };

    xhr.send();

    // Verifica se a temperatura foi obtida e retorna o valor
    if (temperaturaFahrenheit) {
        var temperaturaCelsius = (parseFloat(temperaturaFahrenheit) - 32) * 5 / 9;
        document.getElementById('temp_cur2').textContent = temperaturaCelsius.toFixed(0) + '°';
        return temperaturaCelsius;
    } else {
        console.log("Temperatura não encontrada.");
        return 0;  // Retorna 0 caso a temperatura não tenha sido encontrada
    }
}

// Função para buscar a temperatura da URL alternativa
function downloadPaginaAlternativa(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'https://getclima.vercel.app/api/index.php?url=' + encodeURIComponent(url), true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = xhr.responseText;

            if (response) {
                // Extrair a temperatura e a sensação térmica da URL alternativa
                var temperaturaFahrenheit = buscarTexto(response, 'class="wu-value wu-value-to"', '</span>');
                var temperaturaSensa = buscarTexto(response, 'class="temp"', '°');

                if (temperaturaFahrenheit) {
                    var temperaturaCelsius = (parseFloat(temperaturaFahrenheit) - 32) * 5 / 9;
                    document.getElementById('temp_cur2').textContent = temperaturaCelsius.toFixed(0) + '°';
                } else {
                    document.getElementById('temp_cur2').textContent = 'Offline';
                }

                if (temperaturaSensa) {
                    var temperaturaCelsiusSensa = (parseFloat(temperaturaSensa) - 32) * 5 / 9;
                    // Exibir a temperatura média (opcional)
                    var mediaTemperatura = (temperaturaCelsius + temperaturaCelsiusSensa) / 2;
                    document.getElementById('temp_cur2').textContent = mediaTemperatura.toFixed(0) + '°';
                }
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
