<template>
	<div>
		<br>

	<div class="box">
		<h4 class="has-text-centered" style="font-size:24px;">Virtual Machines</h4><hr>
			<table class="table vm-table" v-show="count_vms > 0">
				<thead>
				<tr>
					<th>Name</th>
					<th>IP Address</th>
					<th>Action</th>
					<th></th>
				</tr>
				</thead>
				<tbody>

				<tr v-for="vm in vms">
					<td>

						<span class="vm_name">{{vm.vm}}</span> <span class="tag is-success" v-if="vm.status == 'up'">Running</span> <span v-else class="tag is-dark"> Stopped </span>
						<br>
						<span class="vm_info"><strong>{{vm.host}}</strong> / {{vm.vm_size}} / {{vm.location}}</span>

					</td>
					<td class="smaller-fonts">{{vm.ip_address}}</td>
					<td class="smaller-fonts" v-show=" vm.status == 'up' ">
						<button href="#" class="button is-info is-outlined" style="font-size:14px;" @click.prevent="stopVM(vm.id, vm.host)">Stop VM</button>
					</td>
					<td class="smaller-fonts" v-show=" vm.status == 'down' ">
						<button href="#" class="button is-info is-outlined" style="font-size:14px;" @click.prevent="startVM(vm.id, vm.host)">Start VM</button>
					</td>
					<td>
						<button class="button is-danger is-outlined" style="font-size:14px;" :disabled="disableDestroy" @click.prevent="deleteVM(vm.id, vm.host)">Destroy</button>
					</td>
				</tr>
				</tbody>
			</table>
		<div v-show="count_vms <= 0 && !PendingRequest">
			<h2 class="has-text-centered">VM list is empty.</h2>
		</div>

		<div v-show="PendingRequest">
			<p class="has-text-centered"><i class="fas fa-spinner"></i> Loading...</p>
		</div>
		<br>

		<!-- Create VM Button -->
		<div class="columns">
			<div class="column is-offset-2" >
				<button class="button is-primary" :disabled="disableCreateVM" @click.prevent="showModal=true" style="margin-left:-25px;">{{button.text}} &nbsp; <span v-show="showSpinner" class="loader"></span></button>
			</div>
		</div>

		<!-- Modal -->
		<div class="modal is-active" v-show="showModal">
			<div class="modal-background" @click.prevent="showModal=false"></div>
			<div class="modal-card">
				<header class="modal-card-head">
					<p class="modal-card-title">Create New Virtual Machine (* required)</p>
					<button class="delete" aria-label="close" @click.prevent="showModal=false"></button>
				</header>
				<section class="modal-card-body">

					<div class="field">
						<label class="label is-small" style="text-align: left">Host: <sup>*</sup></label>
						<div class="control">
							<div class="select is-fullwidth is-multiple">
								<select multiple size="3" v-model="host">
									<option disabled selected value>-- Choose At Least One Host Provider --</option>
									<option v-for="option in options" :value="option.value">{{ option.label }}</option>
								</select>
							</div>
						</div>
					</div>

					<div class="field">
						<label class="label is-small" style="text-align: left">VM Name: <sup>*</sup></label>
						<div class="control">
							<input class="input" type="text" v-model="new_vm_name" required>
						</div>
					</div>

				</section>
				<footer class="modal-card-foot">
					<button class="button is-link" @click.prevent="createNewVM()">Create VM</button>
					<button class="button" @click.prevent="showModal=false">Cancel</button>
				</footer>
			</div>
		</div>

		</div>

	</div>

</template>

<script>
export default {

        data() {
            return {
                vms: [],
				count_vms: 0,
				showModal: false,
				new_vm_name: '',
				button: {
                    text: 'Create VM'
				},
				disableCreateVM: false,
                disableDestroy: false,
                host: [],
                options: [
                    { label: 'Amazon Web Services', value:1 },
                    { label: 'Microsoft Azure', value: 2 }
                ],
                showSpinner: false,
                PendingRequest: false
			}
		},

		methods: {
			stopVM(vm_id, host) {

                var that = this;

                if(host === 'AZURE') {

                    axios.get('/azure/vm/stop/' + vm_id).then(function (response) {

                        if(response.status == 200) {

                            axios.get('/vms/user/1').then(function(res){
                               that.vms = res.data.vms;
                               that.count_vms = res.data.vms.length;
							});

						}

                    });
                } else {

                    axios.post('/aws/vm/action/' + vm_id, {'action': 'STOP'}).then(function(response){

                        if(response.status == 200) {
                            location.reload();
						}

                    });

				}

			},

            startVM(vm_id, host) {

                var that = this;

                if(host === 'AZURE') {

                    axios.get('/azure/vm/start/' + vm_id).then(function (response) {

                        alert('VM Started!');

                        if(response.status == 200) {
                            axios.get('/vms/user').then(function(res){
                                that.vms = res.data.vms;
                                that.count_vms = res.data.vms.length;
                            });
						}

                    });
                } else {

                    axios.post('/aws/vm/action/' + vm_id, {'action': 'START'}).then(function(response){

                        if(response.status == 200){
                            axios.get('/vms/user').then(function(res){
                                app.vms = res.data.vms;
                                app.count_vms = res.data.vms.length;
                            });
						}

					});

				}
            },

            createNewVM() {



                var that = this;

                var new_name = this.new_vm_name.split(' ').join('_');

                this.new_vm_name = '';

                var selected_host = this.host;

                this.host = [];

                // Hide Modal - Disable Create VM Button && Show Loading
                this.showModal = false;
                this.disableCreateVM = true;
                this.button.text = 'Please Wait To Create VM...'
                this.showSpinner = true;

//                if(this.new_vm_name.length <= 0 || selected_host.length <= 0) {
//                    alert('Please Fill In All The Required Fields');
//                    this.showSpinner = false;
//                    this.button.text = 'Create VM'
//                    this.disableCreateVM = false;
//                    this.showModal = true;
//                    return false;
//				}

                if (selected_host.length > 1) {

                    // Create Multiple VMS
                    this.createMultipleVms(selected_host, new_name);

                }
                else {

                    // Create Single VM In One Selected Host
                    var selected = JSON.parse(JSON.stringify(selected_host))

                        if (selected == 1) {

                        	// Create AWS VM
							axios.post('/aws/create/vm/' + new_name).then(function (response) {
                                that.vms = response.data.vms;
                                that.count_vms = response.data.vms.length;
                                that.disableCreateVM = false;
                                that.showSpinner = false;
                                that.button.text = 'Create VM';
                            });
                        }
                        else {

                        	// Create Azure VM
                            axios.post('/azure/create/vm/' + new_name).then(function (response) {
                                that.vms = response.data.vms;
                                that.count_vms = response.data.vms.length;
                                that.disableCreateVM = false;
                                that.showSpinner = false;
                                that.button.text = 'Create VM';
                            });
                        }


                }
            },

			createMultipleVms(selected_host, new_name) {

                for(var host in selected_host) {

                    if (host == 1) { // Create AWS VM
                        axios.post('/aws/create/vm/' + new_name).then(function (response) {

                        });
                    }
                    else {

                        // Create Azure VM
                        axios.post('/azure/create/vm/' + new_name +'2').then(function (response) {

                            axios.get('/vms/user').then(function (response) {
                                location.reload();
                            });

                        });
                    }
                }

			},

			deleteVM(vm_id, host) {

			    this.disableDestroy = true;

				var that = this;

			    if (host === 'AZURE') {

                    var con = confirm('Are you sure?')

                    if (con) {
                        axios.post('/azure/delete/vm/' + vm_id).then(function (response) {
                            that.vms = response.data.vms;
                            that.count_vms = response.data.vms.length;
                            that.disableDestroy = false;
                        });
                    }
                } else {
                    var con = confirm('Are you sure?')

                    if (con) {
                        axios.post('/aws/delete/vm/' + vm_id).then(function (response) {
                            that.vms = response.data.vms;
                            that.count_vms = response.data.vms.length;
                            that.disableDestroy = false;
                        });
                    }
				}

			}

        },

        mounted() {

            var that = this;

            that.PendingRequest = true;

            axios.get('/vms/user').then(function(response){
                that.vms = response.data.vms;
                that.count_vms = response.data.vms.length;
                that.PendingRequest = false;
			});
        }
    }
</script>