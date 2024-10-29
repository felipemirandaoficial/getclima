document.addEventListener("DOMContentLoaded", function() {
    var conte = document.getElementById("cont_f47390d95033e20");
    if (conte) {
        conte.style.cssText = "width: 179px; height: 270px; color: #868686; background-color:#FFFFFF; border:1px solid #D6D6D6; margin: 0 auto; font-family: Arial;";
        
        // Criar um iframe
        var elem = document.createElement("iframe");
        elem.style.cssText = "width:177px; height: 242px; color:#868686; ";
        elem.id = "iframeContent"; // Modificado para um ID diferente
        elem.frameBorder = 0;
        elem.allowTransparency = true;
        elem.scrolling = "no";
        elem.name = "flipe";

        // Função para carregar o conteúdo do PHP
        function carregarConteudo() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'https://api.simasul.com.br/info/modificar.php', true); // Chama o PHP
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    elem.srcdoc = xhr.responseText; // Define o conteúdo do iframe
                }
            };
            xhr.send();
        }

        // Carrega o conteúdo inicialmente
        carregarConteudo();

        // Configura o intervalo para recarregar o conteúdo a cada 1 hora (3600000 milissegundos)
        setInterval(carregarConteudo, 5 * 60000); // 1 hora em milissegundos

        // Adiciona o iframe ao contêiner
        conte.appendChild(elem);
    }
});
