(function app_data(data, $) {

    data.isLoaded = false;

    var database_changed = false;
    var locale_changed = false;

    var fdb = new ForerunnerDB();
    var database = fdb.db('thronesdb');
    var masters = {
        sets: database.collection('master_set', {primaryKey: 'code'}),
        cards: database.collection('master_card', {primaryKey: 'code'})
    };

    var dfd;

    function onCollectionUpdate(updated) {
        database_changed = true;
    }

    function onCollectionInsert(inserted, failed) {
        database_changed = true;
    }

    /**
     * loads the database from local
     * sets up a Promise on all data loading/updating
     * @memberOf data
     */
    function load() {
        masters.sets.load(function (err) {
            if (err) {
                console.log('error when loading sets', err);
            }
            masters.cards.load(function (err) {
                if (err) {
                    console.log('error when loading cards', err);
                }

                /*
                 * data has been fetched from local store
                 */
                
                /*
                 * we set up insert and update listeners now
                 * if we did it before, .load() would have called onInsert
                 */
                masters.sets.on("insert", onCollectionInsert).on("update", onCollectionUpdate);
                masters.cards.on("insert", onCollectionInsert).on("update", onCollectionUpdate);

                /*
                 * if database is not empty, use it for now
                 */
                if (masters.sets.count() > 0 && masters.cards.count() > 0) {
                    release();
                }

                /*
                 * then we ask the server if new data is available
                 */
                query();
            });
        });
    }

    /**
     * release the data for consumption by other modules
     * @memberOf data
     */
    function release() {
        data.sets = database.collection('set', {primaryKey: 'code', changeTimestamp: false});
        data.sets.setData(masters.sets.find());

        data.cards = database.collection('card', {primaryKey: 'code', changeTimestamp: false});
        data.cards.setData(masters.cards.find());

        data.isLoaded = true;

        $(document).trigger('data.app');
    }

    /**
     * queries the server to update data
     * @memberOf data
     */
    function query() {
        dfd = {
            sets: new $.Deferred(),
            cards: new $.Deferred()
        };
        $.when(dfd.sets, dfd.cards).done(update_done).fail(update_fail);

        $.ajax({
            url: Routing.generate('api_sets'),
            success: parse_sets,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error when requesting sets', errorThrown);
                dfd.sets.reject(false);
            }
        });

        $.ajax({
            url: Routing.generate('api_cards'),
            success: parse_cards,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error when requesting cards', errorThrown);
                dfd.cards.reject(false);
            }
        });
    }

    /**
     * called if all operations (load+update) succeed (resolve)
     * deferred returns true if data has been updated
     * @memberOf data
     */
    function update_done() {
        if (database_changed && !locale_changed) {
            /*
             * we display a message informing the user that they can reload their page to use the updated data
             * except if we are on the front page, because data is not essential on the front page
             */
            if ($('.site-title').size() === 0) {
                var message = "A new version of the data is available. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.";
                app.ui.insert_alert_message('warning', message);
            }
        }

        // if it is a force update, we haven't release the data yet
        if (!data.isLoaded) {
            release();
        }
    }

    /**
     * called if an operation (load+update) fails (reject)
     * deferred returns true if data has been loaded
     * @memberOf data
     */
    function update_fail(sets_loaded, cards_loaded) {
        if (sets_loaded === false || cards_loaded === false) {
            var message = "Unable to load the data. Click <a href='javascript:window.location.reload(true)'>here</a> to reload your page.";
            app.ui.insert_alert_message('danger', message);
        } else {
            /*
             * since data hasn't been persisted, we will have to do the query next time as well
             * -- not much we can do about it
             * but since data has been loaded, we call the promise
             */
            release();
        }
    }

    /**
     * updates the database if necessary, from fetched data
     * @memberOf data
     */
    function update_collection(data, collection, locale, deferred) {
        // we update the database and Forerunner will tell us if the data is actually different
        data.forEach(function (row) {
            if(collection.findById(row.code)) {
                collection.update({code: row.code}, row);
            } else {
                collection.insert(row);
            }
        });

        // we update the locale
        if (locale !== collection.metaData().locale) {
            locale_changed = true;
        }
        collection.metaData().locale = locale;

        collection.save(function (err) {
            if (err) {
                console.log('error when saving ' + collection.name(), err);
                deferred.reject(true)
            } else {
                deferred.resolve();
            }
        });
    }

    /**
     * handles the response to the ajax query for sets data
     * @memberOf data
     */
    function parse_sets(response, textStatus, jqXHR) {
        var locale = jqXHR.getResponseHeader('Content-Language');
        update_collection(response, masters.sets, locale, dfd.sets);
    }

    /**
     * handles the response to the ajax query for the cards data
     * @memberOf data
     */
    function parse_cards(response, textStatus, jqXHR) {
        var locale = jqXHR.getResponseHeader('Content-Language');
        update_collection(response, masters.cards, locale, dfd.cards);
    }

    $(function () {
        load();
    });

})(app.data = {}, jQuery);
