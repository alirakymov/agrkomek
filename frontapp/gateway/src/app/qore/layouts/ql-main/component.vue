<template>
    <div id="page-container" class="sidebar-l sidebar-o sidebar-dark sidebar-mini-1 enable-page-overlay side-scroll page-header-fixed main-content-narrow side-trans-enabled">
        <!-- Sidebar -->
        <component is="qc-navbar" :options="navbar"></component>
        <!-- END Sidebar -->

        <!-- Header -->
        <header id="page-header">
            <div class="content-header mx-0">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <component is="qc-navigation" :options="navpills"></component>
                    </div>
                    <component is="qc-buttongroup" :options="navpanel"></component>
                </div>
                <div class="d-flex align-items-center">
                    <!-- logout buttons -->
                </div>
            </div>
        </header>
        <!-- END Header -->

        <!-- Main Container -->
        <main id="main-container">
            <!-- Page Content -->
            <div class="content">
                <template v-for="component in components" :key="'component' + component.id">
                    <component
                        :is="component.type"
                        :options="component.data"
                        @cdestroy="commandDestroyComponent"
                        ref="children"
                    ></component>
                </template>
            </div>
            <!-- END Page Content -->
        </main>
        <!-- END Main Container -->
        <qc-stomp :options="{name: 'notify-service', stomp: stomp}"></qc-stomp>
        <qc-dialog :options="{name: 'global-dialog'}" ref="globalDialog"></qc-dialog>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
