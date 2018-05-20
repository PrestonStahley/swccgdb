(function app_smart_filter(smart_filter, $)
{

    var SmartFilterQuery = [];

    var configuration = {
        e: [add_integer_sf, 'set_code', 'Set'],
        t: [add_integer_sf, 'type_code', 'Card type'],
        b: [add_integer_sf, 'type_code', 'Card subtype'],
        r: [add_string_sf, 'rarity_code', 'Rarity'],
        k: [add_string_sf, 'characteristic', 'Characteristic'],
        s: [add_string_sf, 'side_code', 'Side'],
        d: [add_integer_sf, 'Deploy', 'Deploy'],
        f: [add_integer_sf, 'forfeit', 'Forfeit'],
        p: [add_integer_sf, 'power', 'Power'],
        des: [add_integer_sf, 'destiny', 'Destiny']
    };

    /**
     * called when the list is refreshed
     * @memberOf smart_filter
     */
    smart_filter.get_query = function get_query(query)
    {
        return _.extend(query, SmartFilterQuery);
    };

    /**
     * called when the filter input is modified
     * @memberOf smart_filter
     */
    smart_filter.update = function update(value)
    {
        var conditions = filterSyntax(value);
        SmartFilterQuery = {};

        for(var i = 0; i < conditions.length; i++) {
            var condition = conditions[i];
            var type = condition.shift();
            var operator = condition.shift();
            var values = condition;

            var tools = configuration[type];
            if(tools) {
                tools[0].call(this, tools[1], operator, values);
            }
        }
    };

    smart_filter.get_help = function get_help()
    {
        var items = _.map(configuration, function (value, key)
        {
            return '<li><tt>' + key + '</tt> &ndash; ' + value[2] + '</li>';
        });
        return '<ul>' + items.join('') + '</ul>';
    }

    function add_integer_sf(key, operator, values)
    {
        for(var j = 0; j < values.length; j++) {
            values[j] = parseInt(values[j], 10);
        }
        switch(operator) {
            case ":":
                SmartFilterQuery[key] = {
                    '$in': values
                };
                break;
            case "<":
                SmartFilterQuery[key] = {
                    '$lt': values[0]
                };
                break;
            case ">":
                SmartFilterQuery[key] = {
                    '$gt': values[0]
                };
                break;
            case "!":
                SmartFilterQuery[key] = {
                    '$nin': values
                };
                break;
        }
    }
    function add_string_sf(key, operator, values)
    {
        for(var j = 0; j < values.length; j++) {
            values[j] = new RegExp(values[j], 'i');
        }
        switch(operator) {
            case ":":
                SmartFilterQuery[key] = {
                    '$in': values
                };
                break;
            case "!":
                SmartFilterQuery[key] = {
                    '$nin': values
                };
                break;
        }
    }
    function add_boolean_sf(key, operator, values)
    {
        var value = parseInt(values.shift()), target = !!value;
        switch(operator) {
            case ":":
                SmartFilterQuery[key] = target;
                break;
            case "!":
                SmartFilterQuery[key] = {
                    '$ne': target
                };
                break;
        }
    }
    function filterSyntax(query)
    {
        // renvoie une liste de conditions (array)
        // chaque condition est un tableau à n>1 éléments
        // le premier est le type de condition (0 ou 1 caractère)
        // les suivants sont les arguments, en OR

        query = query.replace(/^\s*(.*?)\s*$/, "$1").replace('/\s+/', ' ');

        var list = [];
        var cond = null;
        // l'automate a 3 états :
        // 1:recherche de type
        // 2:recherche d'argument principal
        // 3:recherche d'argument supplémentaire
        // 4:erreur de parsing, on recherche la prochaine condition
        // s'il tombe sur un argument alors qu'il est en recherche de type, alors le
        // type est vide
        var etat = 1;
        while(query != "") {
            if(etat == 1) {
                if(cond !== null && etat !== 4 && cond.length > 2) {
                    list.push(cond);
                }
                // on commence par rechercher un type de condition
                if(query.match(/^(\w)([:<>!])(.*)/)) { // jeton "condition:"
                    cond = [RegExp.$1.toLowerCase(), RegExp.$2];
                    query = RegExp.$3;
                } else {
                    cond = ["", ":"];
                }
                etat = 2;
            } else {
                if(query.match(/^"([^"]*)"(.*)/) // jeton "texte libre entre guillements"
                        || query.match(/^([^\s]+)(.*)/) // jeton "texte autorisé sans guillements"
                        ) {
                    if((etat === 2 && cond.length === 2) || etat === 3) {
                        cond.push(RegExp.$1);
                        query = RegExp.$2;
                        etat = 2;
                    } else {
                        // erreur
                        query = RegExp.$2;
                        etat = 4;
                    }
                } else if(query.match(/^\|(.*)/)) { // jeton "|"
                    if((cond[1] === ':' || cond[1] === '!')
                            && ((etat === 2 && cond.length > 2) || etat === 3)) {
                        query = RegExp.$1;
                        etat = 3;
                    } else {
                        // erreur
                        query = RegExp.$1;
                        etat = 4;
                    }
                } else if(query.match(/^ (.*)/)) { // jeton " "
                    query = RegExp.$1;
                    etat = 1;
                } else {
                    // erreur
                    query = query.substr(1);
                    etat = 4;
                }
            }
        }
        if(cond !== null && etat !== 4 && cond.length > 2) {
            list.push(cond);
        }
        return list;
    }

    $(function ()
    {
        $('.smart-filter-help').tooltip({
            container: 'body',
            delay: 1000,
            html: true,
            placement: 'bottom',
            title: smart_filter.get_help(),
            trigger: 'hover'
        });
    })

})(app.smart_filter = {}, jQuery);
