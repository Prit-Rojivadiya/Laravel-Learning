<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Users</h2>
        </v-col>
      </v-row>
    </div>

    <UsersGridComponent
      v-if="showUsers"
      :showAddUserInGrid="showAddUserInGrid"
      v-model="showUsers"
    />

    <ManageUserComponent
      v-if="addingUser"
      v-model="addingUser"
      @user-saved="refresh"
    />

  </div>
</template>

<script>

import UsersGridComponent from '~/components/users/UsersGrid'
import ManageUserComponent from '~/components/users/ManageUser'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    UsersGridComponent,
    ManageUserComponent
  },
  data: () => ({
    addingUser: false,
    showUsers: true,
    showAddUserInGrid: true,
  }),
  methods: {
    addUser () {
      this.addingUser = true
    },
    refresh () {
      this.showUsers = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showUsers = true
      })
    }
  }
}
</script>
