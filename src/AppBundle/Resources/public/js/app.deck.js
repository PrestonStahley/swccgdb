/* global _, app */

(function app_deck(deck, $)
{

    var date_creation,
            date_update,
            description_md,
            id,
            name,
            tags,
            side_code,
            side_name,
            unsaved,
            user_id,
            problem_labels = {
          		too_few_cards: "Contains too few cards",
          		too_many_cards: "Contains too many cards",
              too_many_objectives: "A deck can only contain one Objective card",
              objective: "Doesn't comply with the Objective conditions"
          	},
            header_tpl = _.template('<h5><span class="icon icon-<%= code %>"></span> <%= name %> (<%= quantity %>)</h5>'),
            card_line_tpl = _.template('<span class="icon icon-<%= card.type_code %> fg-<%= card.side_code %>"></span> <a href="<%= card.url %>" class="card card-tip" data-toggle="modal" data-remote="false" data-target="#cardModal" data-code="<%= card.code %>"><%= card.label %></a>'),
            layouts = {},
            layout_data = {};

    /*
     * Templates for the different deck layouts, see deck.get_layout_data
     */
    layouts[1] = _.template('<div class="deck-content"><%= meta %><%= plots %><%= characters %><%= attachments %><%= locations %><%= events %></div>');
    layouts[2] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-6 col-print-6"><%= meta %></div><div class="col-sm-6 col-print-6"><%= plots %></div></div><div class="row"><div class="col-sm-6 col-print-6"><%= characters %></div><div class="col-sm-6 col-print-6"><%= attachments %><%= locations %><%= events %></div></div></div>');
    layouts[3] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-4"><%= meta %><%= plots %></div><div class="col-sm-4"><%= characters %></div><div class="col-sm-4"><%= attachments %><%= locations %><%= events %></div></div></div>');

    /**
     * @memberOf deck
     * @param {object} data
     */
    deck.init = function init(data)
    {
        date_creation = data.date_creation;
        date_update = data.date_update;
        description_md = data.description_md;
        id = data.id;
        name = data.name;
        tags = data.tags;
        side_code = data.side_code;
        side_name = data.side_name;
        unsaved = data.unsaved;
        user_id = data.user_id;

        if(app.data.isLoaded) {
            deck.set_slots(data.slots);
        } else {
            console.log("deck.set_slots put on hold until data.app");
            $(document).on('data.app', function ()
            {
                deck.set_slots(data.slots);
            });
        }
    };

    /**
     * Sets the slots of the deck
     *
     * @memberOf deck
     * @param {object} slots
     */
    deck.set_slots = function set_slots(slots)
    {
        app.data.cards.update({}, {
            indeck: 0
        });
        for(var code in slots) {
            if(slots.hasOwnProperty(code)) {
                app.data.cards.updateById(code, {indeck: slots[code]});
            }
        }
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_id = function get_id()
    {
        return id;
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_name = function get_name()
    {
        return name;
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_side_code = function get_side_code()
    {
        return side_code;
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_description_md = function get_description_md()
    {
        return description_md;
    };

    /**
     * @memberOf deck
     */
    deck.get_objectives = function get_objectives()
    {
        return deck.get_cards(null, {
            type_code: 'objective'
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     * @param {object} query
     */
    deck.get_cards = function get_cards(sort, query)
    {
        sort = sort || {};
        sort['code'] = 1;

        query = query || {};
        query.indeck = {
            '$gt': 0
        };

        return app.data.cards.find(query, {
            '$orderBy': sort
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     */
    deck.get_draw_deck = function get_draw_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: {
                '$nin': []
            }
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     */
    deck.get_draw_deck_size = function get_draw_deck_size(sort)
    {
        var draw_deck = deck.get_draw_deck();
        return deck.get_nb_cards(draw_deck);
    };

    deck.get_nb_cards = function get_nb_cards(cards)
    {
        if(!cards)
            cards = deck.get_cards();
        var quantities = _.pluck(cards, 'indeck');
        return _.reduce(quantities, function (memo, num)
        {
            return memo + num;
        }, 0);
    };

    /**
     * @memberOf deck
     */
    deck.get_included_sets = function get_included_sets()
    {
        var cards = deck.get_cards();
        var nb_sets = {};
        cards.forEach(function (card)
        {
            nb_sets[card.set_code] = Math.max(nb_sets[card.set_code] || 0, card.indeck / card.quantity);
        });
        var set_codes = _.uniq(_.pluck(cards, 'set_code'));
        var sets = app.data.sets.find({
            'code': {
                '$in': set_codes
            }
        }, {
            '$orderBy': {
                'available': 1
            }
        });
        sets.forEach(function (set)
        {
            set.quantity = nb_sets[set.code] || 0;
        });
        return sets;
    };

    /**
     * @memberOf deck
     * @param {object} container
     * @param {object} options
     */
    deck.display = function display(container, options)
    {
        console.log('deck.display...');
        options = _.extend({sort: 'type', cols: 2}, options);

        var layout_data = deck.get_layout_data(options);
        console.log('build deck_content...');
        var deck_content = layouts[options.cols](layout_data);

        console.log('append deck_content...');
        $(container)
                .removeClass('deck-loading')
                .empty();

        $(container).append(deck_content);
    };

    deck.get_layout_data = function get_layout_data(options)
    {

        var data = {
            images: '',
            meta: '',
            plots: '',
            characters: '',
            attachments: '',
            locations: '',
            events: ''
        };

        var problem = deck.get_problem();

        deck.update_layout_section(data, 'images', $('<div style="margin-bottom:10px"><img src="/bundles/app/images/sides/' + deck.get_side_code() + '.png" class="img-responsive">'));
        deck.update_layout_section(data, 'meta', $('<h4 style="font-weight:bold">' + side_name + '</h4>'));
        //deck.update_layout_section(data, 'meta', $('<div>Sets: ' + _.map(deck.get_included_sets(), function (set) { return set.name+(set.quantity > 1 ? ' ('+set.quantity+')' : ''); }).join(', ') + '</div>'));
        var sets = _.map(deck.get_included_sets(), function (set)
        {
            return set.name + (set.quantity > 1 ? ' (' + set.quantity + ')' : '');
        }).join(', ');
        deck.update_layout_section(data, 'meta', $('<div>decks.edit.meta.sets</div>'));
        if(problem) {
            deck.update_layout_section(data, 'meta', $('<div class="text-danger small"><span class="fa fa-exclamation-triangle"></span> ' + problem_labels[problem] + '</div>'));
        }

        deck.update_layout_section(data, 'plots', deck.get_layout_data_one_section('type_code', 'plot', 'type_name'));
        deck.update_layout_section(data, 'characters', deck.get_layout_data_one_section('type_code', 'character', 'type_name'));
        deck.update_layout_section(data, 'attachments', deck.get_layout_data_one_section('type_code', 'attachment', 'type_name'));
        deck.update_layout_section(data, 'locations', deck.get_layout_data_one_section('type_code', 'location', 'type_name'));
        deck.update_layout_section(data, 'events', deck.get_layout_data_one_section('type_code', 'event', 'type_name'));

        return data;
    };

    deck.update_layout_section = function update_layout_section(data, section, element)
    {
        data[section] = data[section] + element[0].outerHTML;
    };

    deck.get_layout_data_one_section = function get_layout_data_one_section(sortKey, sortValue, displayLabel)
    {
        var section = $('<div>');
        var query = {};
        query[sortKey] = sortValue;
        var cards = deck.get_cards({name: 1}, query);
        if(cards.length) {
            $(header_tpl({code: sortValue, name: cards[0][displayLabel], quantity: deck.get_nb_cards(cards)})).appendTo(section);
            cards.forEach(function (card)
            {
                var $div = $('<div>').addClass(deck.can_include_card(card) ? '' : 'invalid-card');
                $div.append($(card_line_tpl({card: card})));
                $div.prepend(card.indeck + 'x ');
                $div.appendTo(section);
            });
        }
        return section;
    };

    /**
     * @memberOf deck
     * @return boolean true if at least one other card quantity was updated
     */
    deck.set_card_copies = function set_card_copies(card_code, nb_copies)
    {
        var card = app.data.cards.findById(card_code);
        if(!card)
            return false;

        var updated_other_card = false;

        app.data.cards.updateById(card_code, {
            indeck: nb_copies
        });
        app.deck_history && app.deck_history.notify_change();

        return updated_other_card;
    };

    /**
     * @memberOf deck
     */
    deck.get_content = function get_content()
    {
        var cards = deck.get_cards();
        var content = {};
        cards.forEach(function (card)
        {
            content[card.code] = card.indeck;
        });
        return content;
    };

    /**
     * @memberOf deck
     */
    deck.get_json = function get_json()
    {
        return JSON.stringify(deck.get_content());
    };

    /**
     * @memberOf deck
     */
    deck.get_export = function get_export(format)
    {

    };

    /**
     * @memberOf deck
     */
    deck.get_copies_and_deck_limit = function get_copies_and_deck_limit()
    {
        var copies_and_deck_limit = {};
        deck.get_draw_deck().forEach(function (card)
        {
            var value = copies_and_deck_limit[card.name];
            if(!value) {
                copies_and_deck_limit[card.name] = {
                    nb_copies: card.indeck,
                    deck_limit: card.deck_limit
                };
            } else {
                value.nb_copies += card.indeck;
                value.deck_limit = Math.min(card.deck_limit, value.deck_limit);
            }
        })
        return copies_and_deck_limit;
    };

    /**
     * @memberOf deck
     */
    deck.get_problem = function get_problem()
    {
        var objectives = deck.get_objectives();
        var expectedMaxAgendaCount = 1;
        var expectedMinCardCount = 60;

        // no more than 1 objective
        if(deck.get_nb_cards(deck.get_objectives()) > expectedMaxAgendaCount) {
            return 'too_many_objectives';
        }

        // at least 60 others cards
        if(deck.get_draw_deck_size() < expectedMinCardCount) {
            return 'too_few_cards';
        }

        // the condition(s) of the objective must be fulfilled
        var objectives = deck.get_objectives();
        for(var i=0; i<objectives.length; i++) {
            if(!deck.validate_objective(objectives[i])) {
                return 'objective';
            }
        }
    };

    deck.validate_objective = function validate_objective(objective)
    {
        switch(objective.code) {
            case '07029':
                var hasDantooine = (deck.get_nb_cards(deck.get_cards(null, {code: '01022'})) > 0);
                if(!hasDantooine) {
                    return false;
                }
                break;
            case '07060':
            case '07084':
            case '07091':
            case '07115':
            case '07179':
            case '07228':
            case '07232':
            case '07234':
            case '07275':
                break;
        }
        return true;
    };

    deck.get_invalid_cards = function get_invalid_cards()
    {
        return _.filter(deck.get_cards(), function (card)
        {
            return !deck.can_include_card(card);
        });
    };

    /**
     * returns true if the deck can include the card as parameter
     * @memberOf deck
     */
    deck.can_include_card = function can_include_card(card)
    {

        // no objectives
        if (card.type_code === "objective") {
      		return false;
      	}

        // matching side => yes
        if(card.side_code === side_code)
            return true;

        // if none above => no
        return false;
    };

    /**
     * returns true if the agenda for the deck allows the passed card
     * @memberOfdeck
     */
    deck.card_allowed_by_agenda = function card_allowed_by_agenda(agenda, card) {
        switch(agenda.code) {
            case '01198':
            case '01199':
            case '01200':
            case '01201':
            case '01202':
            case '01203':
            case '01204':
            case '01205':
                return card.side_code === deck.get_minor_side_code(agenda);
            case '09045':
                return card.type_code === 'character' && card.traits.indexOf('card.traits.maester') !== -1;
        }
    }

})(app.deck = {}, jQuery);
