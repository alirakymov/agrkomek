import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            dispatchMap: [
                'columns',
                'breadcrumbs',
                'pagination',
                'reports',
                {key: 'data', path: 'tableData'},
                {key: 'title', path: 'component-title'},
                {key: 'reloadUrl', path: 'url'},
                {key: 'actions', path: 'component-actions'},
                {key: 'draggable', path: 'sortable'},
                {key: 'pages', path: 'pages'}
            ],
            columns: this.options.columns,
            data: this.options.tableData,
            inBlock: _.get(this.options, 'in-block', true),
            breadcrumbs: _.get(this.options, 'breadcrumbs', []),
            reports: _.get(this.options, 'reports', []),
            reloadUrl: _.get(this.options, 'url', null),
            draggable: _.get(this.options, 'sortable', false),
            title: _.get(this.options, 'title', null),
            actions: _.get(this.options, 'component-actions', {}),
            pagination: _.get(this.options, 'pagination', false),
            prevIcon: '<i class="fa fa-angle-double-left"></i>',
            nextIcon: '<i class="fa fa-angle-double-right"></i>',
            filter: _.get(this.options, 'filter', null),
            filterUrl: null,
            page: _.get(this.options, 'pagination.page', 1),
            deleteDialogData: false
        };
    },

    props: ['options'],

    mounted: function() {
    },

    beforeDestroy: function() {
    },

    computed: {
        dragOptions: function() {
            return {
                disabled: this.draggable === false,
            };
        },
    },

    methods: {

        isAction: function(action) {
            return _.get(action, 'actionUri', false) !== false;
        },

        isBadge: function(badge) {
            return _.get(badge, 'isLabel', false) !== false;
        },

        isImage: function (image) {
            return _.get(image, 'image', false) !== false;
        },

        componentActionIcon: function(action) {
            return _.get(action, 'icon', 'fa fa-cog');
        },

        componentActionClick: function(action, e) {
            e.preventDefault();
            this.$axios.get(action.actionUri);
        },

        getHeaderClass: function(column) {
            return column['class-header'] ? column['class-header'] : '';
        },

        getColumnClass: function(column) {
            return column['class-column'] ? column['class-column'] : '';
        },

        getImageSize: function (image, param) {
            return image[param] ? image[param] : '50px';
        },

        tableAction: function(action, event) {
            var $this = this;
            if (action.confirm) {
                let dialog = this.$protocol.findComponent('global-dialog');
                if (dialog) {
                    dialog.show({
                        message: {
                            title: action.confirm.title,
                            message: action.confirm.message,
                            type: _.get(action.confirm, 'type', 'warning'),
                        },
                        actions: [
                            {label: 'Нет', type: 'secondary', action: 'close'},
                            {
                                label: 'Да, хочу',
                                action: function(dialog) {
                                    $this.$axios.get(action.actionUri);
                                    dialog.close();
                                }
                            },
                        ],
                    });
                }
            } else {
                this.$axios.get(action.actionUri);
            }
            event.preventDefault();
        },

        pageCallback: function (pageNum) {
            console.log(pageNum);
            let url = _.get(this.pagination, 'url', false);
            let params = Object.assign({}, _.get(this.pagination, 'url-params', {}));

            if (! url) {
                return;
            }

            params.page = this.page;
            this.$axios.get(url, { params: params });
        },

        reorder: function(e) {

            var order = [];
            _.each(this.data, function(row){
                order.push(row.id);
            });

            this.$axios.post(this.draggable, {data: order}, {
                emulateJSON: true
            });
        },
    }
}
