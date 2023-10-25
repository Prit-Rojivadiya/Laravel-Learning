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
                <v-card-title class="title text-light justify-center" style="word-break: break-word;">
                  <v-img src="/img/logo.png" max-height="70" contain />
                  <p class="pt-5">
                    Account Login
                  </p>
                  <v-alert v-if="false" color="yellow"></v-alert>
                </v-card-title>
                <v-card-text>
                  <v-alert v-if="invalidLogin" class="error">
                    Invalid login, please try again.
                  </v-alert>
                  <v-alert v-if="errors.length" class="error">
                    <template v-for="error in errors">
                      {{ error.msg }}<br />
                    </template>
                  </v-alert>
                  <v-form ref="form" v-model="formValid">
                    <v-text-field
                      v-model="login.email"
                      dense
                      :rules="[rules.required]"
                      label="Username"
                      v-on:keyup.enter="performAction"
                    />
                    <v-text-field
                      v-if="!isForgotPassword"
                      v-model="login.password"
                      dense
                      :rules="[rules.required]"
                      label="Password"
                      type="password"
                      v-on:keyup.enter="performAction"
                    />
                  </v-form>
                </v-card-text>

                <v-card-actions>
                  <v-btn block color="info" :loading="buttonLoader" @click="performAction()">
                    <template v-if="!isForgotPassword">Sign In</template>
                    <template v-if="isForgotPassword">Send reset email</template>
                  </v-btn>
                </v-card-actions>
                <v-card-text>
                  <a v-if="!isForgotPassword" @click="isForgotPassword = true">Forgot Password?</a>
                  <a v-if="isForgotPassword" @click="isForgotPassword = false">Back to Login</a>
                  <br />
                </v-card-text>
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
    isForgotPassword: false,
    snackbar: false,
    snackbarText: null,
    snackbarColor: 'success',
    buttonLoader: null,
    invalidLogin: false,
    errors: [],
    formValid: true,
    login: {
      email: null,
      password: null
    },
    rules: {
      required: value => !!value || 'Required.',
      min: v => (v && v.length >= 8) || 'Min 8 characters'
    }
  }),
  methods: {
    toggleForgot () {
      this.invalidLogin = false
      this.$refs.form.resetValidation();
      this.isForgotPassword = !this.isForgotPassword
    },
    performAction () {
      if (this.isForgotPassword) {
        this.performReset()
      } else {
        this.performLogin()
      }
    },
    async performReset () {
      this.invalidLogin = false

      await this.$refs.form.validate()

      if (!this.formValid) {
        return
      }

      this.buttonLoader = 'loading'

      try {
        const result = await this.$axios.$post('/auth/password/email', { email: this.login.email })

        this.snackbar = true
        this.snackbarText = 'If there is an account associated with the provided email, then a password reset link has been sent. Please check your email'
        this.snackbarColor = 'success'

        this.login.email = null
        this.login.password = null
        this.$refs.form.resetValidation()
        this.isForgotPassword = false

      } catch (e) {
        console.log(e)
        if (e.response && e.response.status === 401) {
          this.invalidLogin = true
        } else if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
      } finally {
        this.buttonLoader = null
      }
    },
    async performLogin () {
      this.invalidLogin = false

      await this.$refs.form.validate()

      if (!this.formValid) {
        return
      }

      this.login.email = this.login.email.toLowerCase()
      this.buttonLoader = 'loading'

      try {
        await this.$auth.loginWith('laravelSanctum', {
          data: this.login
        })

        if (this.$auth.user) {
          this.$router.push('/');
        }
      } catch (e) {
        if (e.response && e.response.status === 401) {
          this.invalidLogin = true
        }
      } finally {
        this.buttonLoader = null
      }
    }
  }
}
</script>
