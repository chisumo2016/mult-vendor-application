<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdmin()">
            <div class="col-md-12" >
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Display All Users</h3>

                        <div class="card-tools">
                             <button class="btn btn-success"  @click="newModal">Add New User <i class="fas fa-user-plus fa-fw"></i></button>
                             <!--<button class="btn btn-success"  data-toggle="modal" data-target="#addNew">Add New User <i class="fas fa-user-plus fa-fw"></i></button>-->
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tbody><tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registered</th>
                                <th>Modidy</th>
                            </tr>

                            <tr v-for="user in users" :key="user.id">
                                <td>{{ user.id }}</td>
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.type |upText }}</td>
                                <td>{{ user.created_at | myDate}}</td>

                                <td>

                                    <a href="#" @click="editModal(user)">
                                        <i class="fa fa-edit blue"></i>
                                    </a>
                                    /
                                    <a href="#"  @click="deleteUser(user.id)">
                                        <i class="fa fa-trash red"></i>
                                    </a>


                                </td>
                            </tr>

                            </tbody></table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>

        <div v-if="!$gate.isAdmin()"><not-found></not-found></div>

        <!--Modal-->

        <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5  v-show="!editmode" class="modal-title" id="addNewLabel">Add New </h5>
                        <h5 v-show="editmode" class="modal-title" id="addNewLabel">Update User Info </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!--Form Start Here   <form @submit.prevent="createUser">-->
                    <form @submit.prevent="editmode ? updateUser() : createUser()">

                        <div class="modal-body">

                            <div class="form-group">
                                <input v-model="form.name"  type="text" name="name"  placeholder="Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.email"  type="email" name="email"  placeholder="Email"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.password"  type="password" name="password"  placeholder="Password" id="password"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                            </div>

                            <div class="form-group">
                            <textarea v-model="form.bio"  type="text" name="bio"  placeholder="Bio" id="bio"
                                      class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                <has-error :form="form" field="bio"></has-error>
                            </div>

                            <div class="form-group">
                                <select v-model="form.type"  type="text" name="type"  placeholder="Type"
                                        class="form-control" :class="{ 'is-invalid': form.errors.has(type) }">

                                    <option value="">Select User Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">Standard User</option>
                                    <option value="author">Author</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button v-show="editmode" type="submit" class="btn btn-success">Update User</button>
                            <button v-show="!editmode"  type="submit" class="btn btn-primary">Create User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <!--data-toggle="modal" data-target="#addNew"-->

</template>

<script>
    export default {
        data(){


            return{
                editmode: false,
                // Create a new user instanc
                users:{},
                // Create a new form instance
                form: new Form({
                    id: '',
                    name : '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: ''
                })
            }
        },

        methods:{

            updateUser(){
                this.$Progress.start();

                this.form.put('api/user/'+this.form.id)  //come from updUser
                .then(() => {
                    //success
                    $('#addNew').modal('show');
                    Swal.fire(
                        'Update!',
                        'Information has been updated.',
                        'success'
                    )

                    this.$Progress.finish();

                    //Event
                    Fire.$emit('AfterCreate');

                })
                .catch(()=>{
                    this.$Progress.fail();


                    })
                //console.log('edit');
            },
            editModal(user){
                this.editmode = true;

                this.form.reset();

                $('#addNew').modal('show');
                this.form.fill(user);
            },

            newModal(){
                this.editmode = false;
                this.form.reset();

                $('#addNew').modal('show');
            },

            deleteUser(id){

                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'

                }).then((result) => {

                    if (result.value) {
                    //Send request to a serve
                    this.form.delete('api/user/'+id).then(()=>{


                            Swal.fire(
                                'Deleted!',
                                'Your file has been deleted.',
                                'success'
                            )


                        //Event
                        Fire.$emit('AfterCreate');
                    }).catch(()=>{

                        swal("Failed!", "There was something wrong .", "Warning");
                    });

                    }
                })
            },
            loadUsers(){
                //ACL
                if(this.$gate.isAdmin()){

                    axios.get("api/user").then(({ data}) => (this.users = data.data));
                }
            },

            createUser(){

                this.$Progress.start();

                this.form.post('api/user')

                    .then(() =>{

                        //Event
                        Fire.$emit('AfterCreate');

                        $('#addNew').modal('hide');

                        toast.fire({
                            type: 'success',
                            title: 'User Created  successfully'
                        })

                        this.$Progress.finish();

                    })

                    .catch(()=>{

                });

            }
        },
        created() {
            this.loadUsers();
            //Listen  Http Request
            Fire.$on('AfterCreate',() => {
                this.loadUsers();
            });
            //Http Request
           // setInterval(()=> this.loadUsers(), 3000); //ES6
            //setInterval(function (){this.loadUsers()}, 3000);
        }
    }
</script>
