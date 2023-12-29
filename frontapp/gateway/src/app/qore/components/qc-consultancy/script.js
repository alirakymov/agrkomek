import { CommandInterface } from '_scripts/qore/protocol.js';
/** Lodash ES */
import _ from 'lodash-es';

import moment from 'moment';

export default {
    mixins: [CommandInterface],

    data: function() {
        return {
            dispatchMap: [
                'consultancy',
                'messages',
            ],
            wysiwygEditor: WysiwygEditor,
            consultancy: _.get(this.options, 'consultancy', null),
            messageRoute: _.get(this.options, 'message-route', null),
            closeRoute: _.get(this.options, 'close-route', null),
            reloadDialogRoute: _.get(this.options, 'reload-dialog-route', null),
            moderatorRoute: _.get(this.options, 'moderator-route', null),
            message: '',
            messages: _.get(this.options, 'messages', []),
            otherMessages: _.get(this.options, 'otherMessages', []),
            moderators: _.get(this.options, 'moderators', []),
            moderator: _.get(this.options, 'consultancy.moderator', {id: 0}),
            currentModeratorId: _.get(this.options, 'consultancy.moderator.id', 0),
            interval: null,
            viewport: 'main',
        };
    },

    props: ['options'],

    mounted: function() {
        this.interval = setInterval(() => {
            this.reloadDialog();
        }, 5000);
    },

    unmounted: function() {
        clearInterval(this.interval);
    },

    computed: {
        currentModerator() {
            return this.moderator.id;
        },
        alertType: function() {
            return 'alert-' + this['info-type'];
        },
        demandWysiwygOptions() {
            return {
                placeholder: 'Введите сюда ваш ответ',

                highlight: {
                    options: [
                        { model: 'yellowMarker', class: 'marker-yellow', title: 'Yellow Marker', color: 'var(--ck-highlight-marker-yellow)', type: 'marker' },
                        { model: 'greenMarker', class: 'marker-green', title: 'Green marker', color: 'var(--ck-highlight-marker-green)', type: 'marker' },
                        { model: 'pinkMarker', class: 'marker-pink', title: 'Pink marker', color: 'var(--ck-highlight-marker-pink)', type: 'marker' },
                        { model: 'blueMarker', class: 'marker-blue', title: 'Blue marker', color: 'var(--ck-highlight-marker-blue)', type: 'marker' },
                        { model: 'redPen', class: 'pen-red', title: 'Red pen', color: 'var(--ck-highlight-pen-red)', type: 'pen' },
                        { model: 'greenPen', class: 'pen-green', title: 'Green pen', color: 'var(--ck-highlight-pen-green)', type: 'pen' }
                    ]
                },

                toolbar: {
                    items: [
                        'undo', 'redo', '|',
                        'bold', 'italic', 'link', 'highlight', '|',
                        'todoList','numberedList','|',
                        'outdent', 'indent', '|',
                        'codeBlock', 'blockQuote', 'insertTable', 'uploadImage', '|',
                        'paragraph', 'heading1', 'heading2',
                    ]
                },

                simpleUpload: {
                    // The URL that the images are uploaded to.
                    uploadUrl: '/~admin/gallery-image/editor-upload',

                },

                image: {
                    toolbar: [
                        'imageStyle:block',
                        'imageStyle:inline',
                        'imageStyle:side',
                        '|',
                        'imageStyle:alignLeft',
                        'imageStyle:alignRight',
                        '|',
                        'imageStyle:alignBlockLeft',
                        'imageStyle:alignCenter',
                        'imageStyle:alignBlockRight',
                        '|',
                        'toggleImageCaption',
                        'imageTextAlternative',
                        '|',
                        'linkImage'
                    ]
                },

                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    ]
                },

                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties'
                    ],
                    // Configuration of the TableProperties plugin.
                    tableProperties: {
                        // ...
                    },

                    // Configuration of the TableCellProperties plugin.
                    tableCellProperties: {
                        // ...
                    },
                },
            }
        },
    },

    methods: {
        selectModerator(event) {

            let moderator = _.find(this.moderators, (o) => o.id == event.target.value);
            this.$axios.post(this.moderatorRoute, {
                id: moderator.id
            });
        },

        getDate(message) {
            moment.locale('ru');
            let created = moment.utc(message.__created.date), now = moment();
            return created.local().calendar();
        },

        reloadDialog() {
            this.$axios.get(this.reloadDialogRoute);
        },

        sendMessage() {
            this.$axios
                .post(this.messageRoute, {
                    message: this.message
                }).then(() => {
                    this.message = '';
                });


        },

        closeConsultancy() {
            this.$axios.get(this.closeRoute);
        }
    }
}
