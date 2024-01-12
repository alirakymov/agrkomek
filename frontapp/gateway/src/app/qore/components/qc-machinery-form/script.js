import { CommandInterface, ProtocolInterface } from '_scripts/qore/protocol.js';
import { isProxy, toRaw } from 'vue';
// import { yandexMap, ymapMarker } from 'vue-yandex-maps'

/** Lodash ES */
import _ from 'lodash-es';

import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

export default {
    mixins: [CommandInterface],

    // components: { yandexMap, ymapMarker },

    data: function() {
        return {
            dispatchMap: [ 'machinery', ],
            machinery: _.get(this.options, 'machinery', null),
            user: _.get(this.options, 'machinery.user', null),
            saveRoute: _.get(this.options, 'save-route', null),
            uploadRoute: _.get(this.options, 'upload-route', null),
            images: _.get(this.options, 'machinery.images', []),
            params: _.get(this.options, 'machinery.params', []),
            types: _.get(this.options, 'types', []),
            statuses: _.get(this.options, 'statuses', []),
            dropzone: null,
            dropzoneUploads: [],
            errors: [],
            coords: [54, 39],
            settings: {
                apiKey: '6438dbae-14c9-4e50-8df4-9672951f5190',
                lang: 'ru_RU',
                coordorder: 'latlong',
                // enterprise: false,
                version: '2.1',
                coords: [48.352571497155054, 64.04235076905002],
            },
            placemark: null,
            map: null,
        };
    },

    props: ['options'],

    mounted: function() {
        this.initializeUploadForm();
        let $this = this;

        // console.log(ymaps);
        ymaps.ready(() => {

            $this.map = new ymaps.Map("yandex-map", {
                center: $this.getMapCenter(),
                zoom: 5,
                // controls: ['fullscreenControl'],
                searchControlProvider: 'yandex#search',
            });

            // Добавление метки по клику на карту
            $this.map.events.add('click', function (e) {
                var coords = e.get('coords');
                console.log(coords);
                $this.machinery.lat = coords[0];
                $this.machinery.lon = coords[1];

                $this.setMarkers();
            });

            $this.setMarkers();
        });
    },

    unmounted: function() {
    },

    computed: {
        uploadFormID() {
            return this.name + '-upload';
        }
    },

    methods: {

        getMapCenter() {
            let center = [48.352571497155054, 64.04235076905002];

            if (this.machinery.lat && this.machinery.lon) {
                center = [this.machinery.lat, this.machinery.lon];
            }

            return center;
        },

        setMarkers() {

            if (! this.machinery.lat || ! this.machinery.lon) {
                return;
            }

            if (this.placemark == null) {
                this.placemark = new ymaps.Placemark([this.machinery.lat, this.machinery.lon]);
                this.map.geoObjects.add(this.placemark);
            } else {
                this.placemark.geometry.setCoordinates([this.machinery.lat, this.machinery.lon]);
            }
        },

        initializeUploadForm() {
            let $this = this;
            let headers = {};
            headers[ProtocolInterface.csrfHeader] = ProtocolInterface.csrfToken;

            this.dropzone = new Dropzone('#' + this.uploadFormID, this.getDropzoneOptions());
        },

        getDropzoneOptions() {
            const $this = this, hoverClass = 'bg-gray-lighter';
            return {
                url: this.uploadRoute,
                disablePreviews: true,
                clickable: true,
                maxFilesize: 250 * 1024 * 1024,
                uploadprogress(file, progress, bytesSent) {
                    $this.count++;
                    file.progress = progress;
                },
                addedfile(file) {
                    file.progress = file.upload.progress;
                    $this.dropzoneUploads.push(file);
                },
                sending(file) {
                    const dzOnload = file.xhr.onload;
                    file.xhr.onload = (e) => {
                        let response = JSON.parse(e.target.responseText);
                        dzOnload(e);
                        $this.dropzoneUploads = _.filter($this.dropzoneUploads, function(_file) {
                            return _file !== file;
                        });
                        $this.images.push(response.file.url);
                    };
                },
                drop(e) {
                    return this.element.parentElement.classList.remove(hoverClass);
                },
                dragend(e) {
                    return this.element.parentElement.classList.remove(hoverClass);
                },
                dragenter(e) {
                    return this.element.parentElement.classList.add(hoverClass);
                },
                dragover(e) {
                    return this.element.parentElement.classList.add(hoverClass);
                },
                dragleave(e) {
                    return this.element.parentElement.classList.remove(hoverClass);
                },
            };
        },

        addParam() {
            this.params.push('');
        },

        isInvalidType() {
            return this.errors.indexOf('type') > -1;
        },

        isInvalidStatus() {
            return this.errors.indexOf('status') > -1;
        },

        deleteImage(key) {
            this.images.splice(key, 1);
        },

        save() {
            if (! this.machinery.type) {
                this.errors.push('type');
                return;
            }

            if (! this.machinery.status) {
                this.errors.push('status');
                return;
            }


            let machinery = this.machinery;

            machinery.images = this.images;
            machinery.params = this.params;

            this.$axios
                .post(this.saveRoute, { machinery })
                .then(() => {
                    this.errors = [];
                });
        },
    }
}
