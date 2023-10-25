<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage User</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn v-if="!loading" small color="info" style="float: right" @click="impersonateUser">Impersonate</v-btn>
          <v-btn v-if="!loading" small color="info" style="float: right" class="mr-5" @click="editUser">Edit</v-btn>
          <v-dialog
            v-if="!loading"
            v-model="deleteDialog"
            persistent
            max-width="290"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn small
                     class="mr-5"
                     color="#fe8181"
                     style="float:right"
                     v-bind="attrs"
                     v-on="on">
                Delete
              </v-btn>
            </template>
            <v-card>
              <v-card-title class="headline">Are you sure?</v-card-title>
              <v-card-text>Do you really want to delete?</v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="green darken-1" text @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="red" @click="deleteItem">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        {{ user.name }}
      </v-card-title>
      <v-card-text>
        <UserOverviewComponent
          v-if="!loading"
          :user="user"
          v-model="user"
        />
      </v-card-text>
    </v-card>

    <ManageUserComponent
      v-if="showEditUser"
      :user="user"
      v-model="showEditUser"
      @user-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import UserOverviewComponent from '~/components/users/Overview'
import ManageUserComponent from '~/components/users/ManageUser'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    UserOverviewComponent,
    ManageUserComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditUser: false,
    user: {
      id: null,
      name: null,
      email: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.user = await this.$axios.$get(`users/${this.$route.params.id}`)
      this.loading = false
    },
    editUser () {
      this.showEditUser = true
    },
    impersonateUser () {
      this.$router.push(`/impersonate?id=${this.user.id}`)
    },
    async deleteItem () {
      await this.$axios.$delete(`users/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
