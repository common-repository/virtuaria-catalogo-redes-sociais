! function(f, b, e, v, n, t, s) {
    if (f.fbq) return;
    n = f.fbq = function() {
        n.callMethod ?
            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
    };
    if (!f._fbq) f._fbq = n;
    n.push = n;
    n.loaded = !0;
    n.version = '2.0';
    n.queue = [];
    t = b.createElement(e);
    t.async = !0;
    t.src = v;
    s = b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t, s)
}(window,document, 'script', '//connect.facebook.net/en_US/fbevents.js');

fbq( 'init', setup['pixel'] );

fbq('track', 'PageView');

if ( jQuery( 'body' ).hasClass( 'woocommerce-checkout' ) &&  ! jQuery( 'body' ).hasClass( 'woocommerce-order-received' ) ) {
    fbq('track', 'InitiateCheckout', {
        'content_ids' : setup['content_ids'], 
        'contents'    : setup['contents'],
        'currency'    : setup['currency'],
        'value'       : setup['value'],
        'num_items'   : setup['num_items'],
    });
}

if ( jQuery( 'body' ).hasClass( 'woocommerce-order-received' ) ) {
    fbq(
        'track',
        'Purchase',
        {
            'content_ids'  : setup['content_ids'], 
            'contents'     : setup['contents'],
            'currency'     : setup['currency'],
            'value'        : setup['value'],
            'num_items'    : setup['num_items'],
            'content_name' : setup['content_name'],
            'content_type' : setup['content_type'],
        }
    );
}

if ( jQuery( 'body' ).hasClass( 'single-product' ) ) {
    fbq('track', 'ViewContent', {
        'content_ids'     : setup['content_ids'], 
        'content_name'    : setup['content_name'],
        'content_category': setup['content_category'],
        'content_type'    : setup['content_type'],
        'currency'        : setup['currency'],
        'value'           : setup['value'],
        'contents'        : setup['contents'],
    });
}

jQuery(document).ready( function( $ ) {
    $('.single_add_to_cart_button').on('click', function() {
        fbq('track', 'AddToCart', {
            'content_ids' : setup['content_ids'], 
            'content_name': setup['content_name'],
            'content_type': setup['content_type'],
            'contents'    : setup['contents'],
            'currency'    : setup['currency'],
            'value'       : setup['value'],
        });
    });

    $('.add_to_cart_button').on('click', function() {
        let title = $(this).attr('aria-label');
        title     = title.replace('Adicionar “', '');
        title     = title.replace('” no seu carrinho', '');
        let qty   = $(this).parent().find('input[name="quantity"]').val();
        qty       = qty ? qty : 1;

        let price;

        if ( $(this).parent().parent().hasClass('sale') ) {
            price  = $(this).parent().parent().find('.price ins').html().replaceAll(/\D/g, '');
            price /= 100;
        } else {
            price  = $(this).parent().parent().find('.price').html().replaceAll(/\D/g, '');
            price /= 100;
        }

        fbq('track', 'AddToCart', {
            'content_ids' : $(this).attr('data-product_id'), 
            'content_name': title,
            'content_type': 'product',
            'contents'    : { 'id': $(this).attr('data-product_id'), 'quantity': qty },
            'currency'    : setup['currency'] ? setup['currency'] : 'BRL',
            'value'       : price,
        });
    });
});