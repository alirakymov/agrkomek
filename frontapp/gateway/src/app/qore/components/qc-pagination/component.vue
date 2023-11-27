<template>
    <nav aria-label="Page navigation" v-if="records / perPage > 1">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" tabindex="-1" aria-label="Previous"
                    v-if="currentFrame - 1 > 1"
                    @click="currentFrame = 1"
                >
                    <span aria-hidden="true">
                        <i class="far fa-angle-double-left"></i>
                    </span>
                    <span class="visually-hidden">Previous</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" tabindex="-1" aria-label="Previous"
                    v-if="currentFrame != 1"
                    @click="currentFrame--"
                >
                    <span aria-hidden="true">
                        <i class="far fa-angle-left"></i>
                    </span>
                    <span class="visually-hidden">Previous</span>
                </a>
            </li>
            <li class="page-item"
                v-for="page in pagesFrameEnd" :key="'paginate-' + page"
                :class="{active: getPageNumber(page) == modelValue}"
            >
                <a class="page-link" href="javascript:void(0)" @click="selectPage(page)">{{ getPageNumber(page) }}</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" aria-label="Next"
                    v-if="currentFrame != lastFrame"
                    @click="currentFrame++"
                >
                    <span aria-hidden="true">
                        <i class="far fa-angle-right"></i>
                    </span>
                    <span class="visually-hidden">Next</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" aria-label="Next"
                    v-if="currentFrame + 1 < lastFrame"
                    @click="currentFrame = lastFrame"
                >
                    <span aria-hidden="true">
                        <i class="far fa-angle-double-right"></i>
                    </span>
                    <span class="visually-hidden">Next</span>
                </a>
            </li>
        </ul>
    </nav>
</template>
<script src="./script.js"></script>
<style src="./style.scss"></style>
