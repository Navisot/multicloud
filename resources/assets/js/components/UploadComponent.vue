<template>
    <div>
        <br>
        <div class="box">
            <h4 class="title has-text-centered">Deploy Your Application (* required)</h4>
            <form method="POST" v-on:submit.prevent="uploadApplicationCode()" enctype="multipart/form-data">
            <!--Name Application-->

            <!--Code Zip-->
            <label>Zip Dockerized Code: <sup>*</sup></label>
            <div class="file is-info">
                <label class="file-label">
                      <input class="file-input" type="file" @change="fileChanged">
                      <span class="file-cta">
                      <span class="file-icon">
                        <i class="fas fa-upload"></i>
                      </span>
                      <span class="file-label">
                        Upload your code in Zip File…
                      </span>
                    </span>
                </label>
            </div>
                <br>
            <div class="field">
                <div class="control">
                    <input class="button is-primary" type="submit" value="Upload Code">
                </div>
            </div>
            </form>
        </div>
    </div>

</template>

<script>
    export default {

        data() {
            return {
                application_code: ''
            }
        },

        methods: {

            uploadApplicationCode(){

                if(this.application_code.length <= 0) {
                    alert('Please Fill In All The Required Fields');
                    return false;
                }

                axios.post('/deploy/vm', {'app_code': this.application_code}).then( (response) => {
                    alert('Your Code Uploaded!');
                });


            },

            fileChanged(e) {

                var fileReader = new FileReader()

                fileReader.readAsDataURL(e.target.files[0])

                fileReader.onload = (e) => {
                    this.application_code = e.target.result
                }


            }

        },

        mounted() {
            console.log('Mounted');
        }
    }
</script>