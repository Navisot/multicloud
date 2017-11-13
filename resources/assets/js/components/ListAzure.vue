<template>
	<div>
		<h1 class="title is-3">Virtual Machines</h1>
			<table class="table vm-table">
				<thead>
				<tr>
					<th>Name</th>
					<th>Username</th>
					<th>IP Address</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<tr v-for="vm in vms">
					<td>
						<span class="vm_name">{{vm.vm}}</span>
						<br>
						<span class="vm_info">{{vm.os_type}} / {{vm.os_disk_size}} GB / {{vm.vm_size}} / {{vm.location}}</span>
					</td>
					<td class="smaller-fonts">{{vm.admin_username}}</td>
					<td class="smaller-fonts">{{vm.ip_address}}</td>
					<td class="smaller-fonts" v-show=" vm.status == 'up' ">
						<a href="#" @click.prevent="stopVM(vm.id)">Stop VM</a>
					</td>
					<td class="smaller-fonts" v-show=" vm.status == 'down' ">
						<a href="#" @click.prevent="startVM(vm.id)">Start VM</a>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
    export default {

        data() {
            return {
                vms: []
			}
		},

		methods: {
			stopVM(vm_name) {

                var that = this;

			    axios.get('/azure/vm/stop/' + vm_name).then(function(response){

			        alert(response.data.status);

			        that.vms = response.data.vms;

				});

			},
            startVM(vm_name) {

                var that = this;

                axios.get('/azure/vm/start/' + vm_name).then(function(response){

                    alert(response.data.status);

                    that.vms = response.data.vms;

                });
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