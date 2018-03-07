<template>
    <div>
        <br>
        <div class="box">
            <h4 class="has-text-centered" style="font-size:24px;">Deploy Your Application</h4><hr>
            <form method="POST" v-on:submit.prevent="uploadApplicationCode()" enctype="multipart/form-data">
            <!--Name Application-->
                <div class="field">
                    <label class="label is-small" style="text-align: left; font-size:15px;">Choose VM:</label>
                    <div class="control">
                        <div class="select is-fullwidth is-multiple">
                            <select multiple size="3" v-model="selected_vms">
                                <option disabled selected value>-- Choose At Least One VM To Deploy Your App --</option>
                                <option v-for="vm in vms" :value="vm.id">{{ vm.vm }} ({{vm.vm_size}})</option>
                            </select>
                        </div>
                    </div>
                </div>


            <!--Code Zip-->
            <label class="label is-small" style="text-align: left; font-size:15px;">Zip File:</label>
            <div class="file is-info">
                <label class="file-label">
                      <input class="file-input" type="file" @change="fileChanged">
                      <span class="file-cta">
                      <span class="file-icon">
                        <i class="fas fa-upload"></i>
                      </span>
                      <span class="file-label">
                        Upload your code..
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
                application_code: '',
                vms: [],
                selected_vms: []
            }
        },

        methods: {

            uploadApplicationCode(){

                if(this.application_code.length <= 0 || this.selected_vms.length <= 0) {
                    alert('Please Fill In All The Required Fields');
                    return false;
                }

                axios.post('/deploy/vm', {'app_code': this.application_code, 'selected_vms':this.selected_vms}).then( (response) => {
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
            var that = this;
            axios.get('vms/user').then(function(response){
                that.vms = response.data.vms;
            });
        }
    }
</script>