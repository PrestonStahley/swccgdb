/* global app, _ */

(function ui_deckimport(ui, $)
{

    var name_regexp;

    ui.on_content_change = function on_content_change(event)
    {
        var text = $(content).val(),
                slots = {},
                side_code,
                side_name;

        text.match(name_regexp).forEach(function (token)
        {
            var qty = 1, name = token.trim(), card;
            if(token[0] === '(') {
                return;
            }
            if(name.match(/^(\d+)x (.*)/)) {
                qty = parseInt(RegExp.$1, 10);
                name = RegExp.$2.trim();
            }
            card = app.data.cards.findOne({name: name});
            side = app.data.sides.findOne({name: name});
            if(card) {
                slots[card.code] = qty;
            } else if(side) {
                side_code = side.code;
                side_name = side.name;
            } else {
                console.log('rejecting string [' + name + ']');
            }
        });

        app.deck.init({
            side_code: side_code,
            side_name: side_name,
            slots: slots
        });
        app.deck.display('#deck');
        $('input[name=content').val(app.deck.get_json());
        $('input[name=side_code').val(side_code);
    };

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        $('#content').change(ui.on_content_change);
    };

    /**
     * called when the app data is loaded
     * @memberOf ui
     */
    ui.on_data_loaded = function on_data_loaded()
    {
        var characters = _.unique(_.pluck(app.data.cards.find(), 'name').join('').split('').sort()).join('');
        name_regexp = new RegExp('\\(?[\\d' + characters.replace(/[[\](){}?*+^$\\.|]/g, '\\$&') + ']+\\)?', 'g');
    };

    /**
     * called when both the DOM and the data app have finished loading
     * @memberOf ui
     */
    ui.on_all_loaded = function on_all_loaded()
    {
    };


})(app.ui, jQuery);
