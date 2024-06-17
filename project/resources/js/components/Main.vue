<template>
    <Button label="Запустить импорт" @click="startImport" :disabled="this.info.start_import_button_disabled"></Button>

    <div class="m-4">
        <p v-if="this.info.current_upload_id">ID загрузки: <b>{{ this.info.current_upload_id }}</b></p>
        <p v-if="this.info.last_processed_row">
            Количество загруженных строк из файла: <b>{{ this.info.last_processed_row }}</b>
        </p>
        <p v-if="this.info.percent_exec">Выполнено {{ this.info.percent_exec }} %</p>
    </div>

    <ProgressBar :value="this.info.percent_exec" v-if="this.info.percent_exec"></ProgressBar>

</template>
<script>
import axios from 'axios';

export default {
    name: 'Index',

    data() {
        return {
            info: {
                'start_import_button_disabled': false
            },
        };
    },
    mounted() {
        this.getInfo();

        Echo.private('import')
            .listen('ParseUsersReport', (e) => {
                this.info = e.info;
            });
    },
    methods: {
        getInfo() {
            axios.get('/api/current-upload/info').then(res => {
                this.info = res.data;
            })
        },
        startImport() {
            axios.get('/api/import').then(res => {
                this.info.start_import_button_disabled = true;
            });
        }
    }
}
</script>
