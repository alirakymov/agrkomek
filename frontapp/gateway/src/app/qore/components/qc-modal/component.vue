<template>
    <transition
        name="slide-fade"
        v-on:after-leave="destroy"
    >
        <div v-if="show" class="modal-mask scroll-dependency">
            <div class="modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" :class="[typeStyle, sizeStyle]" role="document">
                    <div class="modal-content">
                        <div class="block block-rounded block-transparent mb-0">
                            <div class="block-header block-header-default-1">
                                <h3 class="block-title">{{ title }}</h3>
                                <div class="block-options">
                                    <ul v-if="panel" class="nav-main nav-main-horizontal nav-main-hover">
                                        <li class="nav-main-item" v-for="btn in panel" >
                                            <a class="nav-main-link" href="javascript:void(0);" aria-haspopup="true" aria-expanded="false"
                                                :data-toggle="btn.submenu ? 'submenu' : ''"
                                                @click="reaction(btn, $event)"
                                            >
                                                <i v-if="btn.icon" class="nav-main-link-icon fa-fw" :class="btn.icon + (btn.label ? '' : ' me-0')" ></i>
                                                <span class="nav-main-link-name">{{ btn.label }}</span>
                                            </a>
                                            <ul class="nav-main-submenu nav-main-submenu-right" v-if="btn.submenu">
                                                <li class="nav-main-item" v-for="subBtn in btn.submenu">
                                                    <a class="nav-main-link btn-primary" href="javascript:void(0);" 
                                                        @click="reaction(subBtn, $event)"
                                                    >
                                                        <i v-if="subBtn.icon" class="nav-main-link-icon" :class="subBtn.icon"></i>
                                                        <span class="nav-main-link-name">{{ subBtn.label }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="javascript:void(0)"
                                                @keydown.esc="commandClose()"
                                                @click="commandClose()"
                                            ><i class="nav-main-link-icon fa fa-fw fa-times me-0"></i></a>
                                        </li>
                                    </ul><button v-else type="button" class="btn-block-option" 
                                        @keydown.esc="commandClose()"
                                        @click="commandClose()"
                                    ><i class="fa fa-fw fa-times"></i></button>
                                </div>
                            </div>
                            <div class="block-content pt-0">
                                <component v-for="component in components"
                                    :key="component.id"
                                    :is="component.type"
                                    :options="component.data"
                                ></component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script src="./script.js"></script>
<style src="./style.scss"></style>
