/** Lodash ES */
import _ from 'lodash-es';

export default {

    data: function() {
        return {
            currentFrame: 1,
            pagesPerFrame: 10
        };
    },

    props: ['options', 'perPage', 'records', 'modelValue'],

    emits: ['update:modelValue'],

    computed: {

        frames() {
            return Math.ceil(this.records / this.perPage);
        },

        pagesFrameEnd() {
            return (this.currentFrame * this.pagesPerFrame) > this.frames
                ? this.frames % this.pagesPerFrame
                : this.pagesPerFrame;
        },

        lastFrame() {
            return Math.ceil(this.frames / this.pagesPerFrame);
        },
    },

    beforeMount: function() {
    },

    mounted: function() {
    },


    beforeDestroy() {
    },

    methods: {
        getPageNumber(page) {
            return (this.currentFrame - 1) * this.pagesPerFrame + page;
        },

        selectPage(page) {
            page = this.getPageNumber(page);
            this.$emit('update:modelValue', page);
            this.$emit('paginate', page);
        }
    }
}
