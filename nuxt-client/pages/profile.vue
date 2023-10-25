<template>
  <div>
    <h2>{{ $auth.user.name }}</h2>
    <v-container>
      <v-card flat>
        <div class="overline mb-4 mt-4">
          Profile
        </div>
        <v-form ref="formProfile" v-model="profileFormValid">
          <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="formProfile.name"
                :rules="[v => !!v || 'Please enter name']"
                label="Name"
                required
                dense
              />
            </v-col>
          </v-row>
          <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="formProfile.email"
                :rules="[v => !!v || 'Please enter a valid email address']"
                label="Email"
                required
                dense
              />
            </v-col>
          </v-row>
        </v-form>
      </v-card>
    </v-container>

    <v-alert v-if="errors.length" class="error">
      <template v-for="error in errors">
        {{ error.msg }}<br />
      </template>
      Please try again
    </v-alert>

    <v-btn small color="primary" class="ml-4" @click="saveProfile" :loading="btnLoader">Save</v-btn>
    <v-btn small color="info" style="float: right" class="ml-4 mr-4" @click.stop="resettingPassword = true">
      Change Password
      <v-icon dark right>
        mdi-account-lock
      </v-icon>
    </v-btn>
    <UpdatePasswordDialog @success="showSuccessPwdUpdate()" v-model="resettingPassword" />

    <v-snackbar
      v-model="snackbar"
      timeout="4000"
      :color="snackbarColor"
    >
      {{ snackbarText }}

      <template v-slot:action="{ attrs }">
        <v-btn
          color="pink"
          text
          v-bind="attrs"
          @click="snackbar = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import UpdatePasswordDialog from '@/components/UpdatePasswordDialog'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    UpdatePasswordDialog
  },
  data () {
    return {
      tab: null,
      resettingPassword: false,
      snackbar: false,
      snackbarText: null,
      snackbarColor: 'success',
      btnLoader: null,
      profileFormValid: false,
      errors: [],
      formProfile: {
        name: null,
        email: null,
      },
      profile: null,
    }
  },
  computed: {
    isProduction () {
      return process.env.apiUrl === 'https://api.tranzitfleet.com/api'
    }
  },
  // async asyncData ({ app }) {
  // },
  created () {
    this.profile = JSON.parse(JSON.stringify(this.$auth.user))
    this.formProfile.name = this.profile.name
    this.formProfile.email = this.profile.email
  },
  methods: {
    async saveProfile () {
      this.btnLoader = 'loading'
      this.errors = []
      await this.$refs.formProfile.validate()
      if (!this.profileFormValid) {
        this.btnLoader = null
        return
      }

      try {
        await this.$axios.$put(`/profile/${this.$auth.user.id}`, this.formProfile)
        this.btnLoader = null
        this.snackbarColor = 'success'
        this.snackbarText = 'Your profile has been successfully updated'
        this.snackbar = true

        await this.$auth.fetchUser()
      }
      catch (e) {
        console.log('provile save error', e.message)
        if (e.response && e.response.status === 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push({ msg: e })
        }
        this.btnLoader = null
        this.snackbarColor = 'failed'
        this.snackbarText = "Profile failed to update, please resolve the errors and try again"
        this.snackbar = true
      }
    },
    showSuccessPwdUpdate () {
      this.snackbarColor = 'success'
      this.snackbarText = 'Your password has been successfully updated'
      this.snackbar = true
    }
  }
}
</script>
