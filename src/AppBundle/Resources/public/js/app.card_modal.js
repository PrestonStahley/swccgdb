(function app_card_modal(card_modal, $)
{

    var modal = null;

    /**
     * @memberOf card_modal
     */
    card_modal.display_modal = function display_modal(event, element)
    {
        event.preventDefault();
        $(element).qtip('destroy', true);
        fill_modal($(element).data('code'));
    };

    /**
     * @memberOf card_modal
     */
    card_modal.typeahead = function typeahead(event, card)
    {
        fill_modal(card.code);
        $('#cardModal').modal('show');
    };

    function fill_modal(code)
    {
        var card = app.data.cards.findById(code),
                modal = $('#cardModal');
        if(!card)
            return;

        modal.data('code', code);
        modal.find('.card-modal-link').attr('href', card.url);
        modal.find('h3.modal-title').html(app.format.name(card));
        modal.find('.modal-image').html('<img class="img-responsive" src="' + card.image_url + '">');
        modal.find('.modal-info').html(
                '<div class="card-info"><p>' + app.format.info(card) + '</p></div>'
                + '<div class="card-traits">' + app.format.traits(card) + '</div>'
                + '<div class="card-text border-' + card.side_code + '">' + app.format.text(card) + '</div>'
                + '<div class="card-set"><p>' + app.format.set(card) + '</p></div>'
                );

        var qtyelt = modal.find('.modal-qty');
        if(qtyelt) {

            var qty = '<button type="button" class="btn btn-default btn-sm btn-card-remove" data-command="-" title="Remove from deck"><span class="fa fa-minus"></span></button><button type="button" class="btn btn-default btn-sm btn-card-add" data-command="+" title="Add to deck"><span class="fa fa-plus"></span></button>';
            qtyelt.html(qty);

        } else {
            if(qtyelt)
                qtyelt.closest('.row').remove();
        }
    }

    $(function ()
    {

        $('body').on({click: function (event)
            {
                var element = $(this);
                if(event.shiftKey || event.altKey || event.ctrlKey || event.metaKey) {
                    event.stopPropagation();
                    return;
                }
                card_modal.display_modal(event, element);
            }}, '.card');

    })

})(app.card_modal = {}, jQuery);
