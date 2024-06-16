<template>
    <Button label="Запустить импорт" @click="startImport"></Button>

    <div class="m-4">
        <p>ID загрузки: <b>{{ this.info.current_upload_id }}</b></p>
        <p>Количество загруженных строк из файла: <b>{{ this.info.last_processed_row }}</b></p>
        <p>Выполнено {{ this.info.percent_exec }} %</p>
    </div>

    <ProgressBar :value="this.info.percent_exec"></ProgressBar>

</template>
<script>
import axios from 'axios';

export default {
    name: 'Index',

    data() {
        return {
            progress: 0,
            info: {},
            percentExec: 0
        };
    },
    mounted() {
        this.getInfo();

        Echo.private('import')
            .listen('ParseUsersReport', (e) => {
                console.log(e);

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
            axios.get('/api/import');
        }
    }
}
</script>
