document.addEventListener("DOMContentLoaded", function() {
    buscarTemperatura(); // Chama a função para buscar a temperatura atual
});
function buscarTemperatura(url = 'https://www.wunderground.com/dashboard/pws/IAQUID2') {
    // URL alternativa caso a primeira falhe
    var urlAlternativa = 'https://www.wunderground.com/weather/SBCG';

    // Requisição HTTP para obter o conteúdo do site
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
	let loop;
	loop = 0;
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
                    buscarTemperatura(urlAlternativa);
                    return;  // Para parar a execução desta função ao tentar a URL alternativa
                }

                // Para o segundo formato (na URL alternativa)
                if (url === urlAlternativa) {
                    // Usar PHP para baixar a página inteira
                    downloadPaginaAlternativa(urlAlternativa);
                }

                // Exibir as temperaturas ou mostrar "Offline"
                if (temperaturaFahrenheit) {
                    var temperaturaCelsius = (parseFloat(temperaturaFahrenheit) - 32) * 5 / 9;
                } else {
						console.log("Temperatura Fahrenheit não encontrada.");
						buscarTemperatura('https://www.wunderground.com/weather/SBCG');
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

