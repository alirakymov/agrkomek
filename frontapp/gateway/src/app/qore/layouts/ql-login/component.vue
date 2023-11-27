<template>
    <div id="page-container">
        <!-- Main Container -->
        <main id="main-container">
            <!-- Page Content -->
            <div class="hero-static d-flex align-items-center">
                <div class="content">
                    <div class="row justify-content-center push">
                        <div class="col-md-8 col-lg-6 col-xl-4">
                            <!-- Sign In Block -->
                            <div class="block block-rounded mb-0">
                                <div class="block-header block-header-default">
                                    <h3 class="block-title">Вход в систему</h3>
                                    <div class="block-options">
                                        <!-- <a class="btn-block-option fs-sm" href="op_auth_reminder.html">Forgot Password?</a> -->
                                    </div>
                                </div>
                                <div class="block-content">
                                    <div class="p-sm-3 px-lg-4 px-xxl-5 pb-lg-5">
                                        <p class="text-muted">Введите вашу почту и пароль для входа</p>

                                        <component v-for="component in components"
                                            :key="component.id"
                                            :is="component.type"
                                            :options="component.data"
                                            ref="children"
                                        ></component>
                                    </div>
                                </div>
                            </div>
                            <!-- END Sign In Block -->
                        </div>
                    </div>
                    <div class="fs-sm text-muted text-center">
                        <strong>MPSolutions</strong> © <span data-toggle="year-copy" class="js-year-copy-enabled">2024</span>
                    </div>
                </div>
            </div>
            <!-- END Page Content -->
        </main>
        <!-- END Main Container -->
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
