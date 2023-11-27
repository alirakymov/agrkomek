import { CommandInterface } from '_scripts/qore/protocol.js';
import { defineAsyncComponent } from 'vue';
/** Lodash ES */
import _ from 'lodash-es';

let components = {};
[
    'qf-blockeditor',
    'qf-checkbox',
    'qf-codeeditor',
    'qf-datetime',
    'qf-dropzone',
    'qf-email',
    'qf-hidden',
    'qf-password',
    'qf-select',
    'qf-slider',
    'qf-submit',
    'qf-switch',
    'qf-text',
    'qf-textarea',
    'qf-treeselect',
    'qf-wysiwyg',
    'qf-wysiwyg-inline',
].forEach((component) => {
    components[component] = defineAsyncComponent(() => {
        return import(['.', 'qf-fields', component, 'component'].join('/') + '.vue')
    });
});

export default {
    mixins: [CommandInterface],

    components,

    data: function() {
        return {
            dispatchMap: [
                'errors',
                'model',
                {key: 'title', path: 'component-title'},
            ],
            action: _.get(this.options, 'action', ''),
            method: _.get(this.options, 'method', 'POST'),
            errors: _.get(this.options, 'errors', {}),
            fields: this.prepareFields(_.get(this.options, 'fields', null)),
            model: _.get(this.options, 'model', {}),
            formInstance: this,
        };
    },

    props: ['options'],

    mounted: function() {
    },

    methods: {

        prepareFields: function(formFields) {
            if (formFields === null) {
                return [];
            }
            let fields = [], $this = this, iteration = 0;
            _.each(formFields, function(data, name){
                data = _.merge(data, {
                    formAction: _.get($this.options, 'action', ''),
                    name: name,
                    formName: _.get($this.options, 'name', ''),
                });
                fields.push({
                    name: name,
                    data: data,
                    ctype: 'qf-' + data.type,
                    id: name + '-' + iteration
                });
                iteration++;
            });
            return fields;
        },

        onSubmit: function(e) {
            e.preventDefault();
            var button = this.$el.querySelector('button[type="submit"]');
            var formData = new FormData(this.$el)
                , formInstance = this.$el
                , vueInstance = this;

            let method = this.method.toLowerCase();

            if (method == 'get') {
                let data = {};
                formData.forEach((value, key) => data[key] = value);
                this.$axios[method](this.$el.action, { params: data });
            } else {
                this.$axios[method](this.$el.action, formData)
                    .then(function(response){
                        button.disabled = false;
                    });
            }
        },

        commandReloadFields: function(options) {
            this.fields = this.prepareFields(options.fields);
        },

        commandDropFields: function(options) {
            for (let i in options.fields) {
                let fieldIndex = _.findIndex(this.fields, {name: options.fields[i]});
                if (fieldIndex !== -1) {
                    this.fields.splice(fieldIndex, 1);
                }
            }
        },

        commandSetFields: function(options) {

            let fields = this.prepareFields(options.fields);
            for (let i in fields) {
                let position = _.get(fields[i], 'data.position', {set: 'bottom'}),
                    fieldIndex = _.findIndex(this.fields, {name: fields[i].name}),
                    field = fields[i];
                if (fieldIndex !== -1) {
                    this.$set(this.fields, fieldIndex, field);
                    // this.fields[fieldIndex] = field;
                } else if (position.set == 'top') {
                    this.fields = _.union([field], this.fields);
                } else if (position.set == 'bottom') {
                    this.fields = _.union(this.fields, [field]);
                } else if (position.set == 'after' || position.set == 'before' ) {
                    let i, index = (i = _.findIndex(this.fields, {name: position.target})) < 0 ? this.fields.length-1 : i,
                        left = position.set == 'after' ? index + 1 : index;
                    this.fields = _.union(
                        _.take(this.fields, left),
                        [field],
                        this.fields.slice(left)
                    );
                }
            }
        },

        commandUpdateModel: function(options) {
            for (let name in options.model) {
                this.model[name] = options.model[name];
            }
        },

    }
}
