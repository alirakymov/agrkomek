<template>
    <div>
        <div :id="uploadFormID" class="dropzone mb-125 machinery-dropzone">
            <draggable class="row g-sm items-push js-gallery push js-gallery-enabled"
                v-model="images"
                :no-transition-on-drag="false"
                tag="div" >
                <div class="col-md-4 col-lg-3 col-xl-2 me-3 mt-3 animated fadeIn"
                     v-for="(image, key) in images"
                >
                    <div class="options-container rounded-3">
                        <img class="img-fluid options-item" :src="image" alt="">
                        <div class="options-overlay bg-black-75">
                            <div class="options-overlay-content">
                                <div class="btn-group" role="group" data-toggle="buttons">
                                    <a class="btn btn-sm btn-warning" @click="deleteImage(key)"><i class="far fa-trash-alt"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>

        <div class="form-floating mb-4">
            <select class="form-select" v-model="machinery.type" :class="{'is-invalid': isInvalidType()}" :disabled="user">
                <option v-for="option in types" :value="option.id" >{{ option.label }}</option>
            </select>
            <label >Тип объявления</label>
            <div class="form-text animated fadeInUp">выберите тип объявления</div>
        </div>
        <div class="form-floating mb-4">
            <select class="form-select" v-model="machinery.status" :class="{'is-invalid': isInvalidStatus()}">
                <option v-for="option in statuses" :value="option.id" >{{ option.label }}</option>
            </select>
            <label >Статус</label>
            <div class="form-text animated fadeInUp">выберите статус объявления</div>
        </div>
        <div class="form-floating mb-4" v-if="machinery.status == 'rejected'">
            <textarea class="form-control" style="height: 120px"
                placeholder="Описание"
                v-model="machinery.rejectMessage"
                rows="10"
            ></textarea>
            <label>Причина</label>
            <div class="form-text animated fadeInUp">опишите причину отклонения</div>
        </div>
        <div class="form-floating mb-4">
            <input type="text" class="form-control" :disabled="user"
                placeholder="Название"
                v-model="machinery.title"
            >
            <label>Название</label>
            <div class="form-text animated fadeInUp">название техники</div>
        </div>
        <div class="form-floating mb-4" v-if="machinery.type != 'exchange'">
            <input type="text" class="form-control" :disabled="user"
                placeholder="Цена"
                v-model="machinery.price"
            >
            <label>Цена</label>
            <div class="form-text animated fadeInUp">введите стоимость техники</div>
        </div>
        <div class="form-floating mb-4" v-for="(param, key) in params">
            <input type="text" class="form-control" :disabled="user"
                placeholder="Новый параметр"
                v-model="params[key]"
            >
            <label>Параметр</label>
            <div class="form-text animated fadeInUp">дополнительный параметр</div>
        </div>
        <div class="mb-4"><a href="javascript:void(0);" @click="addParam()" v-if="! user">+ добавить параметр</a></div>
        <div class="form-floating mb-4">
            <input type="text" class="form-control" :disabled="user"
                placeholder="Ссылка на geo-локацию"
                v-model="machinery.linkGeo"
            >
            <label>Ссылка на geo-локацию</label>
            <div class="form-text animated fadeInUp">введите ссылку на geo-локацию</div>
        </div>
        <div class="form-floating mb-4">
            <input type="text" class="form-control" :disabled="user"
                placeholder="Ссылка на Whatsapp"
                v-model="machinery.linkWhatsapp"
            >
            <label>Ссылка на Whatsapp</label>
            <div class="form-text animated fadeInUp">введите ссылку на Whatsapp</div>
        </div>
        <div class="form-floating mb-4">
            <input type="text" class="form-control" :disabled="user"
                placeholder="Номер телефона"
                v-model="machinery.phone"
            >
            <label>Телефон</label>
            <div class="form-text animated fadeInUp">введите телефон</div>
        </div>
        <div class="form-floating mb-4">
            <textarea class="form-control" style="height: 120px" :disabled="user"
                placeholder="Описание"
                v-model="machinery.content"
                rows="10"
            ></textarea>
            <label>Описание</label>
            <div class="form-text animated fadeInUp">опишите технику</div>
        </div>
        <div class="form-floating mb-4">
            <!-- <div id="yandex-map"></div> -->
            <yandex-map
                v-model="map"
                :settings="{
                    location: {
                        center: mapCenter,
                        zoom: 10,
                    },
                }"
                width="100%"
                height="500px"
            >
                <yandex-map-default-scheme-layer/>
                <yandex-map-default-features-layer/>
                <yandex-map-listener :settings="{ onClick: clickOnMap() }" />
                <yandex-map-marker
                    :settings="marker"
                >
                    <div
                        class="icon"
                        :style="{
                            '--size': 'size' in marker ? marker.size : '20px',
                            '--color': 'color' in marker && marker.color,
                            '--icon': 'icon' in marker && `url(${marker.icon})`,
                        }"
                    ></div>
                </yandex-map-marker>
            </yandex-map>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-alt-primary" @click="save()">Сохранить</button>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.scss"></style>
