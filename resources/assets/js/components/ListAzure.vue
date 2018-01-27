<template>
	<div>
		<br>

	<div class="box">
		<h1 class="title has-text-centered">Virtual Machines</h1>
			<table class="table vm-table">
				<thead>
				<tr>
					<th>Name</th>
					<th>Username</th>
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
						<span class="vm_info">{{vm.os_type}} / {{vm.os_disk_size}} GB / {{vm.vm_size}} / {{vm.location}}</span>

					</td>
					<td class="smaller-fonts">{{vm.admin_username}}</td>
					<td class="smaller-fonts">{{vm.ip_address}}</td>
					<td class="smaller-fonts" v-show=" vm.status == 'up' ">
						<button href="#" class="button is-info is-outlined" style="font-size:14px;" @click.prevent="stopVM(vm.id)">Stop VM</button>
					</td>
					<td class="smaller-fonts" v-show=" vm.status == 'down' ">
						<button href="#" class="button is-info is-outlined" style="font-size:14px;" @click.prevent="startVM(vm.id)">Start VM</button>
					</td>
					<td>
						<button class="button is-danger is-outlined" style="font-size:14px;" :disabled="disableDestroy" @click.prevent="deleteAzureVM(vm.id)">Destroy</button>
					</td>
				</tr>
				</tbody>
			</table>
		<br>

		<!-- Create VM Button -->
		<div class="columns">
			<div class="column is-offset-2" >
				<button class="button is-primary" :disabled="disableCreateVM" @click.prevent="showModal=true" style="margin-left:-25px;">{{button.text}}</button>
			</div>
		</div>

		<!-- Modal -->
		<div class="modal is-active" v-show="showModal">
			<div class="modal-background" @click.prevent="showModal=false"></div>
			<div class="modal-card">
				<header class="modal-card-head">
					<p class="modal-card-title">Create New Virtual Machine</p>
					<button class="delete" aria-label="close" @click.prevent="showModal=false"></button>
				</header>
				<section class="modal-card-body">
					<div class="control">
						<input class="input" type="text" v-model="new_vm_name" placeholder="VM Name">
					</div>
				</section>
				<footer class="modal-card-foot">
					<button class="button is-success" @click.prevent="createNewVM()">Create VM</button>
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
				showModal: false,
				new_vm_name: '',
				button: {
                    text: 'Create VM'
				},
				disableCreateVM: false,
                disableDestroy: false

			}
		},

		methods: {
			stopVM(vm_name) {

                var that = this;

			    axios.get('/azure/vm/stop/' + vm_name).then(function(response){

			        that.vms = response.data.vms;

				});

			},

            startVM(vm_name) {

                var that = this;

                axios.get('/azure/vm/start/' + vm_name).then(function(response){

                    alert('VM Started!');

                    that.vms = response.data.vms;

                });
            },

            createNewVM() {

                var that = this;

                var new_name = this.new_vm_name.split(' ').join('_');

                this.new_vm_name = '';

                // Hide Modal - Disable Create VM Button && Show Loading
				this.showModal = false;
                this.disableCreateVM = true;
                this.button.text = 'Please Wait To Create VM...'


			    axios.post('/azure/create/vm/' + new_name).then(function(response){
                    that.vms = response.data.vms;

                    that.disableCreateVM = false;
                    that.button.text = 'Create VM';
				});
			},

			deleteAzureVM(vm_id) {

			    this.disableDestroy = true;

			    var con = confirm('Are you sure?')

				var that = this;

				if (con) {
                    axios.post('/azure/delete/vm/' + vm_id).then(function(response){
                        that.vms = response.data.vms;
                    });
				}

			}

        },

        mounted() {

            var that = this;

            axios.get('/vms/user/1').then(function(response){
                that.vms = response.data;
			});
        }
    }
</script>