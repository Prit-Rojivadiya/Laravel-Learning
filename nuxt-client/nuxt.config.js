import colors from 'vuetify/es5/util/colors'

export default {
  mode: 'spa',
  /*
  ** Headers of the page
  */
  head: {
    titleTemplate: '%s - Tranzit',
    title: '',
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: 'TranzIT App' }
    ],
    script: [
      { hid: 'phones', src: '/js/cleave-phone.us.js', defer: true },
      { hid: 'phones', src: '/js/phone-type-formatter.us.js', defer: true }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
      //{ rel: 'stylesheet', href: 'https://kendo.cdn.telerik.com/themes/5.0.1/material/material-arctic.css' }
    ]
  },
  /*
  ** Customize the progress-bar color
  */
  loading: { color: '#fff' },
  /*
  ** Global CSS
  */
  css: [
    '@/assets/css/main.css'
  ],
  /*
  ** Plugins to load before mounting the App
  */
  plugins: [
    '~/plugins/axios',
    '~/plugins/filters',
    '~/plugins/laravel-permissions',
    {
      src: '~/plugins/kendoui.js',
      mode: 'client'
    }
  ],
  /*
  ** Nuxt.js dev-modules
  */
  buildModules: [
    // Doc: https://github.com/nuxt-community/eslint-module
    // '@nuxtjs/eslint-module',
    '@nuxtjs/vuetify',
    // [ '@nuxtjs/google-analytics', {
    //   id: ''
    //   // debug: {
    //   //   enabled: true,
    //   //   sendHitTask: true
    //   // }
    // }]
  ],
  /*
  ** Nuxt.js modules
  */
  modules: [
    // Doc: https://axios.nuxtjs.org/usage
    '@nuxtjs/axios',
    '@nuxtjs/auth-next',
    '@nuxtjs/sentry'
  ],
  // sentry: {
  //   dsn: '', // Enter your project's DSN here
  //   // Additional Module Options go here
  //   // https://sentry.nuxtjs.org/sentry/options
  //   config: {
  //     // Add native Sentry config here
  //     // https://docs.sentry.io/platforms/javascript/guides/vue/configuration/options/
  //     logErrors: true
  //   }
  // },
  /*
  ** Axios module configuration
  ** See https://axios.nuxtjs.org/options
  */
  axios: {
    credentials: true,
  },
  /*
  ** vuetify module configuration
  ** https://github.com/nuxt-community/vuetify-module
  */
  vuetify: {
    customVariables: ['~/assets/variables.scss'],
    theme: {
      // dark: true,
      themes: {
        light: {
          primary: '#0168aa',
          secondary: '#a12716'
        }
        // dark: {
        //   primary: colors.blue.darken2,
        //   accent: colors.grey.darken3,
        //   secondary: colors.amber.darken3,
        //   info: colors.teal.lighten1,
        //   warning: colors.amber.base,
        //   error: colors.deepOrange.accent4,
        //   success: colors.green.accent3
        // }
      }
    }
  },
  // See for nuxt auth: https://auth.nuxtjs.org/providers/laravel-jwt
  auth: {
    strategies: {
      laravelSanctum: {
        provider: 'laravel/sanctum',
        url: '/',
        endpoints: {
          csrf: {
            url: '/sanctum/csrf-cookie'
          },
          login: {
            url: '/auth/login'
          },
          user: {
            url: '/auth/user'
          },
          logout: {
            url: '/auth/logout'
          }
        }
      }
    }
  },
  env: {
    apiUrl: process.env.API_URL || 'http://localhost:8000/api',
  },
  /*
  ** Build configuration
  */
  build: {
    /*
    ** You can extend webpack config here
    */
    extend (config, ctx) {
      //if (!ctx.isDev) {
      //  config.devtool = false
      //  config.devtool = 'cheap'
      //}
    }
  }
}
