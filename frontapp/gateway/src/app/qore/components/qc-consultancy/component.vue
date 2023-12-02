<template>
    <div>
        <div class="p-zero-margins">
            <div class="message">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-responsive">
                    <img class="avatar-img" src="assets/img/avatars/2.jpg" alt="">
                </a>
                <div class="message-inner">
                    <div class="message-body">
                        <div class="message-content">
                            <div class="message-text">
                                <p> {{ consultancy.question }} </p>
                            </div>
                        </div>
                    </div>

                    <div class="message-footer">
                        <span class="fs-sm text-muted">{{ getDate(consultancy) }}</span>
                    </div>
                </div>
            </div>

            <div class="message" v-for="message in messages" :class="{'message-out': message.direction == 2}">
                <div class="message-inner">
                    <div class="message-body">
                        <div class="message-content">
                            <div class="message-text">
                                <p v-html="message.message"></p>
                            </div>
                        </div>
                    </div>

                    <div class="message-footer">
                        <span class="fs-sm text-muted">{{ getDate(message) }}</span>
                    </div>
                </div>
            </div>


            <div class="bordered-ck-editor mt-3">
                <ckeditor :editor="wysiwygEditor" :config="demandWysiwygOptions" v-model="message"/>
                <button type="button" class="btn btn-alt-primary mt-3" @click="sendMessage()">Ответить</button>
            </div>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
