(function app_format(format, $)
{

    /**
     * @memberOf format
     */
    format.traits = function traits(card)
    {
        return card.traits || '';
    };

    /**
     * @memberOf format
     */
    format.name = function name(card)
    {
        return (card.uniqueness ? card.uniqueness : '') + card.name;
    }

    format.side = function side(card)
    {
        var text = '<span class="fg-' + card.side_code + ' icon-' + card.side_code + '"></span> ' + card.side_name + '. ';
        if(card.side_code != 'neutral') {
            if(card.is_loyal)
                text += 'card.info.loyal' + '. ';
            else
                text += 'card.info.nonloyal' + '. ';
        }
        return text;
    }

    /**
     * @memberOf format
     */
    format.set = function set(card)
    {
        var text = card.set_code + ', ' + card.rarity_code;
        return text;
    }

    /**
     * @memberOf format
     */
    format.info = function info(card)
    {
        var text = '';
        switch(card.type_code) {
            case 'effect':
            case 'interrupt':
            case 'weapon':
            case 'vehicle':
                if(card.subtype_name) {
                  text += '<span class="card-subtype">' + card.subtype_name + '. </span>';
                }
                text += '<span class="card-type">' + card.type_name + '. </span>';
                break;
            case 'starship':
                text += '<span class="card-type">' + card.type_name + '. </span>';
                text += '<span class="card-subtype">' + card.subtype_name + ': ' + card.model_type + '</span>';
                break;
            default:
                text += '<span class="card-type">' + card.type_name + '. </span>';
                if(card.subtype_name) {
                  text += '<span class="card-subtype">' + card.subtype_name + '. </span>';
                }
                break;
        }
        return text;
    };

    /**
     * @memberOf format
     */
    format.text = function text(card)
    {
        var text = card.gametext || '';
        text = text.replace(/\[(\w+)\]/g, '<span class="icon-$1"></span>')
        text = text.split("\n").join('</p><p>');
        return '<p>' + text + '</p>';
    };

})(app.format = {}, jQuery);
