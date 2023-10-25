<template>
  <v-app id="inspire">
    <div>
      <v-content>
        <v-container
          class="fill-height"
          fluid
        >
          <v-row>
            <v-col md="4" xs="12" sm="12" offset-lg="4">
              <v-card
                outlined
                class="pa-2"
              >
                <v-card-title class="headline text-light justify-center" style="word-break: break-word;">
                  <v-img src="/img/logo.png" max-height="70" contain />
                  <p class="pt-5">
                    Update your password below
                  </p>
                </v-card-title>
                <v-card-text>
                  <v-alert v-if="errors.length" class="error">
                    <template v-for="error in errors">
                      {{ error.msg }}<br />
                    </template>
                  </v-alert>
                  <v-form ref="form" v-model="formValid">
                    <v-text-field v-model="login.email" dense :rules="[rules.required]" label="Email" />
                    <v-text-field
                      v-model="login.password"
                      dense
                      :rules="[rules.required]"
                      label="Password"
                      type="password"
                    />
                    <v-text-field
                      v-model="login.password_confirmation"
                      dense
                      :rules="[rules.required]"
                      label="Password Confirmation"
                      type="password"
                    />
                  </v-form>
                </v-card-text>

                <v-card-actions>
                  <v-btn block color="info" :loading="buttonLoader" @click="performUpdate()">
                    Update password
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
      </v-content>

      <v-snackbar
        v-model="snackbar"
        timeout="6000"
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
  </v-app>
</template>

<style lang="scss">
</style>

<script>
export default {
  layout: 'guest',
  auth: 'guest',
  data: () => ({
    errors: [],
    snackbar: false,
    snackbarText: null,
    snackbarColor: 'success',
    buttonLoader: null,
    formValid: true,
    login: {
      email: null,
      password: null,
      password_confirmation: null
    },
    rules: {
      required: value => !!value || 'Required.',
      min: v => (v && v.length >= 8) || 'Min 8 characters'
    }
  }),
  methods: {
    async performUpdate () {
      this.invalidLogin = false

      await this.$refs.form.validate()

      if (!this.formValid) {
        return
      }

      this.buttonLoader = 'loading'

      try {
        const result = await this.$axios.$post('/auth/password/reset', { ...this.login, token: this.$route.query.token })

        this.snackbar = true
        this.snackbarText = 'Password has been successfully reset. Please wait while we log you into your account.'
        this.snackbarColor = 'success'

        await this.$auth.loginWith('laravelJWT', {
          data: this.login
        })
      } catch (e) {
        if (e.response && e.response.status === 422) {
          this.errors = e.response.parsedErrors
        }
      } finally {
        this.buttonLoader = null
      }
    }
  }
}
</script>
