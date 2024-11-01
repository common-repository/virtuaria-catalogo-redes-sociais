=== Virtuaria - Integração de Catálogo com Redes Sociais ===
Contributors: tecnologiavirtuaria
Tags: integration, facebook, instagram, meta, pixel, catalog, feed, marketing
Requires at least: 4.7
Tested up to: 6.4.1
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integra sua loja virtual woocommerce com a sua loja no Facebook / Instagram, via catálogo de produtos da Meta. Permite instalar o  Pixel da meta e gerar estatísticas diversas.

== Description ==

Gere e mantenha atualizado automaticamente um link (feed xml) com todos os produtos da sua loja virtual, com este plugin. O feed segue todas as recomendações da Meta para permitir enviar o seu catálogo de produtos da sua loja virtual para uma loja no Facebook e/ou Instagram. Este plugin também permite instalar o pixel do Facebook e assim conectar a loja virtual às ferramentas do Meta Business (Facebook Business). O Pixel gera dados de rastreamento a partir das interações dos usuários.

Recursos:
* Gera e atualiza automaticamente um feed de produtos de sua loja;
* Permite configurar categorias de produtos que não devem estar em seu feed;
* Define o Pixel usado para conectar a loja virtual às ferramentas do Meta Business (Facebook Business). O Pixel gera dados de rastreamento a partir das interações dos usuários. É utilizado para mensurar o desempenho de campanhas e para integrar a experiência de compra com as redes da Meta;
* Frequência de atualizações do feed definida via configuração;
* Opção para gerar o feed, manualmente via botão na tela de configuração.


Este plugin foi desenvolvido sem nenhum incentivo do Facebook. Nenhum dos desenvolvedores deste plugin possuem vínculos com esta empresa. E note que este plugin foi feito baseado na documentação da API pública do Facebook.

**Observação:** Os prints foram feitos em um painel wordpress/woocommerce personalizado pela Virtuaria objetivando otimizar o uso em lojas virtuais, por isso o fundo verde.

**Para mais informações, acesse** [virtuaria.com.br - desenvolvimento de plugins, criação e hospedagem de lojas virtuais](https://virtuaria.com.br/).

= Compatibilidade =

Compatível com Woocommerce 5.8.0 ou superior

### Descrição em Inglês: ###

Connect your online store to Facebook shopping with this plugin.

With this module your store will generate and update a feed (XML) with all the products in the store. Using this Feed it is possible to publish your products on Facebook through a page of your choice.

Resources:
* Automatically generates and updates a feed of products from your store;
* Allows you to configure product categories that should not be in your feed;
* Defines the Pixel used to connect the virtual store to the Meta Business (Facebook Business) tools. Pixel generates tracking data from user interactions. It is used to measure the performance of campaigns and to integrate the shopping experience with Meta's networks;
* Frequency of feed updates defined via configuration;
* Option to manually generate the feed via a button on the configuration screen.



== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.
* Navegue para o menu Integração Facebook e defina seu código Pixel.

= Requerimentos: =

Ter instalado o [WooCommerce](http://wordpress.org/plugins/woocommerce/).

[youtube https://www.youtube.com/watch?v=YbDs2tCtXv4]

Novo link para feed de dados: sualoja.com.br/virtuaria-facebook-shopping/


### Instalação e configuração em Inglês: ###

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Navigate to the Facebook Integration menu and set your pixel code.


== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin está licenciado como GPLv3.

= O que eu preciso para utilizar este plugin? =

* Ter instalado uma versão atual do plugin WooCommerce.
* Possuir uma conta no facebook.

= Qual a frequência de atualização do Feed? =

Por padrão o Feed é atualizado uma vez ao dia, por volta das 02:00 horas, porém, via configuração é possível aumentar essa frequência em até 4x.

= Qual URL para acessar o Feed gerado? =

O feed pode ser encontrado em https://seudominio.com.br/virtuaria-facebook-shopping. A página de configuração contém link direto para seu feed.

= Ativei o plugin mas o feed está em branco, o que pode ser? =

* Servidor com pouca memória disponível;
* Pasta do plugin sem permissão de escrita em seu servidor, isto é necessário para geração do arquivo XML.
* Para outras situações, verificar todos os logs indo em “WooCommerce” > “Status do sistema” > “Logs”.

== Screenshots ==

1. Configurações do plugin;
2. Catálogo no Facebook;
3. Estatísticas do Pixel;


== Upgrade Notice ==
Nenhuma atualização disponível

== Changelog ==
= 1.2.0 2023-11-23 =
* Definição, via configuração, do tamanho das imagens de produtos presentes no feed;
* Suporte a galeria de imagens do produto.
= 1.1.3 2023-11-22 =
* Melhorando escapes para caracteres especiais;
* Convertendo título e descrição de produtos para padrão camel case;
* Remoção do campo g:adult do feed.
= 1.1.2 2023-06-26 =
* Ignorando produtos agrupados para o feed XML.
= 1.1.1 2023-05-22 =
* Ajuste de compatibilidade com php 8+.
= 1.1.0 2022-12-29 =
* Novos parâmetros para os eventos do Pixel.
= 1.0.0 2022-11-28 =
* Versão inicial.