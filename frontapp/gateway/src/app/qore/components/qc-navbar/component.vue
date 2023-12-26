<template>
    <nav id="sidebar" aria-label="Main Navigation">
        <!-- Side Header -->
        <div class="content-header ps-2">
            <!-- Logo -->
            <a class="fw-semibold text-dual" href="index.html">
                <span class="fs-5">Agro</span>
            </a>
            <!-- END Logo -->

            <!-- Extra -->
            <div>
                <!-- Dark Mode -->
                <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                <!-- <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="layout" -->
                <!--     data-action="dark_mode_toggle"> -->
                <!--     <i class="far fa-moon"></i> -->
                <!-- </button> -->
                <!-- END Dark Mode -->

                <!-- Close Sidebar, Visible only on mobile screens -->
                <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout" data-action="sidebar_close"
                    href="javascript:void(0)">
                    <i class="fa fa-fw fa-times"></i>
                </a>
                <!-- END Close Sidebar -->
            </div>
            <!-- END Extra -->
        </div>
        <!-- END Side Header -->

        <!-- Sidebar Scrolling -->
        <div class="js-sidebar-scroll" data-simplebar="init">
            <div class="simplebar-wrapper" style="margin: 0px;">
                <div class="simplebar-height-auto-observer-wrapper">
                    <div class="simplebar-height-auto-observer"></div>
                </div>
                <div class="simplebar-mask">
                    <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                        <div class="simplebar-content-wrapper" style="height: 100%; overflow: hidden scroll;">
                            <div class="simplebar-content" style="padding: 0px;">
                                <!-- Side Navigation -->
                                <div class="content-side">
                                    <ul class="nav-main">
                                        <component v-for="element in qnElements"
                                            :key="element.id"
                                            :is="element.type"
                                            :item-data="element.data"
                                        ></component>
                                    </ul>
                                </div>
                                <!-- END Side Navigation -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="simplebar-placeholder" style="width: auto; height: 1270px;"></div>
            </div>
            <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
            </div>
            <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                <div class="simplebar-scrollbar"
                    style="height: 913px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
            </div>
        </div>
        <!-- END Sidebar Scrolling -->
    </nav>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
