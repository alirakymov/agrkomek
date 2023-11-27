import _ from 'lodash-es';

export let ProtocolInterface = {
    /** Root Vue instance */
    vue: null,
    /** Csrf header name */
    csrfHeader: 'csrf-token',
    /** Csrf token */
    csrfToken: null,
    /** Registered components */
    components: [],
    /** Parcels of components and commands */
    parcels: [],
    /** Start protocol */
    start(vue) {
        this.vue = vue;
        if (this.csrfToken == null) {
            this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        }
    },
    /** Process response requested by axios */
    process(response) {
        /** Process csrf header */
        this.csrfProcess(response);
        /** Process data */
        this.parcels = this.parcels.concat(response.data);
        /** Start response dispatching */
        if (typeof this.vue._protocolDispatch == 'function') {
            this.vue._protocolDispatch();
        }
    },
    /** Flush parsels */
    flush() {
        this.parcels = [];
    },
    /** Initialize component port with front protocol */
    init(component) {
        /** Register component in register */
        this.registerComponent(component);
        /** Register component listener in global event bus */
        this.vue.$bus.on(this.getEventName(component.name), function (command) {
            component.processCommand(command);
        });
    },
    /** Csrf process */
    csrfProcess(response) {
        this.csrfToken = _.get(response, 'headers.' + this.csrfHeader, this.csrfToken);
    },
    /** Request Interceptor Headers */
    getRequestHeaders() {
        let headers = { 'X-Requested-With': 'XMLHttpRequest'};
        if (this.csrfToken) {
            headers[this.csrfHeader] = this.csrfToken;
        }
        return headers;
    },
    /** Register component */
    registerComponent(component) {
        if (! _.find(this.components, (c) => c.name == component.name)) {
            this.components.push(component);
        }
    },
    /** Unlink component */
    unlinkComponent: function (component) {
        this.components = _.reject(this.components, (c) => c.name == component.name);
    },
    /** Search component in registry */
    findComponent: function (name) {
        return _.find(this.components, (c) => c.name == name);
    },

    findChildren(name) {
        return _.filter(this.components, {parent: name});
    },

    /** Deinitialize component port with front protocol */
    deinit: function (component) {
        /** Remove component from registry */
        this.unlinkComponent(component);
        /** Unbind all events */
        this.vue.$bus.off(this.getEventName(component.name));
    },

    /** Dispatch new states of components */
    dispatch: function (target) {
        /** Get target component parcel */
        let component = _.find(this.parcels, (p) => p.is == 'component' && p.name == target.name);
        if (component) {
            /** Dispatch component data */
            target.commandDispatchMap(component);
            /** Dispatch component */
            target.commandDispatch(component);
        }

        component = _.merge({'merge-strategy': 'replace'}, component ? component : {});
        /** Get target sub components */
        let components = _.filter(this.parcels, (p) => p.is == 'component' && p.parent == target.name);
        /** Dispatch target sub components by strategy */
        target.commandDispatchComponents({
            strategy: component['merge-strategy'],
            components: components,
        });

        /** Dispatch commands */
        let commands = _.filter(this.parcels, (p) => p.is == 'command' && p.name == target.name);
        if (commands.length) {
            this.executeCommands(target.name, commands);
        }
        /** Delete dispatched commands */
        this.parcels = _.reject(this.parcels, (p) => p.is == 'command' && p.name == target.name);

        /** Combine current level interface components and dispatch each of them */
        _.filter(this.components, {parent: target.name}).forEach((component) => {
            component._protocolDispatch();
        });

        this.parcels = _.reject(this.parcels, (p) => p.is == 'component' && p.name == target.name);
    },

    /** Execute component commands via Vue.$bus */
    executeCommands: function (componentName, commands) {
        for (let command of commands) {
            this.vue.$bus.emit(this.getEventName(componentName), command);
        }
    },

    /** Get event name for component event */
    getEventName: function (name) {
        return 'event-' + name.replace(/\\/gi, '-');
    },

};

export let CommandInterface = {
    created: function () {
        this.$protocol.init(this);
    },

    mounted: function () {
        this.$protocol.dispatch(this);
    },

    updated: function () {
    },

    unmounted() {
        this.$protocol.deinit(this);
    },

    data: function () {
        return {
            dispatchMap: [],
            reloadUrl: _.get(this.options, 'url', null),
            name: _.get(this.options, 'name', null),
            parent: _.get(this.options, 'parent', null),
            type: _.get(this.options, 'type', null),
            title: _.get(this.options, 'component-title', null),
            actions: _.get(this.options, 'component-actions', {}),
            inBlock: _.get(this.options, 'inBlock', false),
            components: this.prepareComponents(_.get(this, 'options.components', [])),
        };
    },

    methods: {
        _protocolDispatch() {
            this.$protocol.dispatch(this);
        },

        /** Prepare components */
        prepareComponents: function(components) {
            if (components === null || ! Array.isArray(components)) {
                return [];
            }


            let pcomponents = [], $this = this;
            components.forEach((data) => {
                pcomponents.push({
                    type: data.type,
                    data: data,
                    id: data.name,
                    name: data.name,
                });
            });

            return pcomponents;
        },
        /** Process component command */
        processCommand: function (componentCommand) {
            // - if exists command method for current component command
            let commandName = componentCommand.command.charAt(0).toUpperCase()
                + componentCommand.command.slice(1);
            if (typeof this['command' + commandName] == 'function') {
                this['command' + commandName](componentCommand.options);
            }
        },
        /** Destroy component command */
        commandDestroyComponent(command) {
            for (let i in this.components) {
                if (_.get(this.components[i], 'data.name', null) === command.name) {
                    this.$protocol.unlinkComponent(this.components[i]);
                    this.components.splice(i, 1);
                }
            }
        },
        /** Set components command */
        commandDispatchComponents(options) {
            if (options.strategy === 'replace' && options.components.length) {
                this.components = this.prepareComponents(options.components);
            } else if (options.strategy === 'concat') {
                let components = [];
                for (let component of _.get(options, 'components', [])) {
                    if (! _.filter(this.components, {name: component.name}).length) {
                        components.push(component);
                    }
                }

                this.components = this.components.concat(this.prepareComponents(components));
            } else if (options.strategy === 'clear') {
                this.components = [];
            }
        },
        /** Add sub-components to component collection */
        _protocolAddComponents(options) {
        },
        /** Reinit component data by map */
        commandDispatchMap: function (component) {
            for (let property of this.dispatchMap) {
                if (! _.isPlainObject(property)) {
                    property = {key: property, path: property};
                }

                if (typeof property.path == 'function') {
                    this[property.key] = property.path(component, property);
                } else {
                    let value = _.get(component, property.path, null);
                    if (value !== null) {
                        this[property.key] = value;
                    }
                }

            }
        },
        /** Run component commands */
        commandDispatch(component) {
        },
        /** Run component commands */
        commandExecute(component) {
            let componentCommands = _.get(component, 'commands', []);
            for (let command of componentCommands) {
                this.processCommand(command);
            }
        },
        /** Redirect command */
        commandRedirect(options) {
            if (_.get(options, 'blank', false) === true) {
                 window.open(options.url, '_blank').focus();
            } else {
                window.location.href = options.url;
            }
        },
        /** Reload command */
        commandReload() {
            if (this.reloadUrl !== null) {
                this.$axios.get(this.reloadUrl);
            }
        },

        /** Process other component commands */
        processAction(action) {
            let componentName = _.get(action, 'component', false);
            let command = _.get(action, 'command', false);
            if (!componentName || !command) {
                return;
            }

            this.$protocol.executeCommands(componentName, [command]);
        },

        componentActionIcon(action) {
            return _.get(action, 'icon', 'fa fa-cog');
        },

        reaction(action, e) {
            e.preventDefault();
            if (action.redirect) {
                window.location.href = action.actionUri;
            } else {
                this.$axios.get(action.actionUri);
            }
        },

        getChildren() {
            return this.$protocol.findChildren(this.name);
        },

        cdestroy() {
            this.$emit('cdestroy', {name: this.name});
        },
    }
};
