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
        return (card.is_unique ? '<span class="icon-unique"></span> ' : "") + card.name;
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
    format.pack = function pack(card)
    {
        var text = card.pack_name + ' #' + card.position + '. ';
        return text;
    }

    /**
     * @memberOf format
     */
    format.info = function info(card)
    {
        var text = '<span class="card-type">' + card.type_name + '. </span>';
        switch(card.type_code) {
            case 'character':
                text += 'card.info.cost' + ': ' + (card.cost != null ? card.cost : 'X') + '. ';
                text += 'card.info.str' + ': ' + (card.strength != null ? card.strength : 'X') + '. '
                if(card.is_military)
                    text += '<span class="color-military icon-military" title="' + 'challenges.military' + '"></span> ';
                if(card.is_intrigue)
                    text += '<span class="color-intrigue icon-intrigue" title="' + 'challenges.intrigue' + '"></span> ';
                if(card.is_power)
                    text += '<span class="color-power icon-power" title="' + 'challenges.power' + '"></span> ';
                break;
            case 'attachment':
            case 'location':
            case 'event':
                text += 'card.info.cost' + ': ' + (card.cost != null ? card.cost : 'X') + '. ';
                break;
            case 'plot':
                text += 'card.info.income' + ': ' + card.income + '. ';
                text += 'card.info.initiative' + ': ' + card.initiative + '. ';
                text += 'card.info.claim' + ': ' + card.claim + '. ';
                text += 'card.info.reserve' + ': ' + card.reserve + '. ';
                text += 'card.info.plotlimit' + ': ' + card.deck_limit + '. ';
                break;
        }
        return text;
    };

    /**
     * @memberOf format
     */
    format.text = function text(card)
    {
        var text = card.text || '';
        text = text.replace(/\[(\w+)\]/g, '<span class="icon-$1"></span>')
        text = text.split("\n").join('</p><p>');
        if(card.designer) {
            text = text + '<p class="card-designer">' + card.designer + '</p>';
        }
        return '<p>' + text + '</p>';
    };

})(app.format = {}, jQuery);
