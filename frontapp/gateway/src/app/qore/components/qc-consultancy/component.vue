<template>
    <div>
        <div class="p-zero-margins">
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


            <div class="bordered-ck-editor mt-3" v-if="consultancy.closed == 0">
                <ckeditor :editor="wysiwygEditor" :config="demandWysiwygOptions" v-model="message"/>
                <div>
                    <button type="button" class="btn btn-alt-primary mt-3 me-2" @click="sendMessage()">Ответить</button>
                    <button type="button" class="btn btn-alt-warning mt-3" @click="closeConsultancy()">Закрыть консультацию</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
