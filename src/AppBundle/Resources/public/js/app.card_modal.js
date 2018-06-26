(function app_card_modal(card_modal, $)
{

    var modal = null;

    /**
     * @memberOf card_modal
     */
    card_modal.display_modal = function display_modal(event, element)
    {
      console.log('display_modal');
      console.log(event);
      console.log(element);
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
      console.log('fill_modal');
      console.log(code);
        var card = app.data.cards.findById(code),
                modal = $('#cardModal');
      console.log(card);
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
