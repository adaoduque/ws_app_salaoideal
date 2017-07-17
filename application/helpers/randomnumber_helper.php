<?php
    class Randomnumber {
        /**
         * Esse método gera um valor randomico com base em microsegundos
         * Geralmente, esse valor será usado para alimentar a função mt_srand() para geração de numeros randomicos mais precisos
         *
         * @param  NULL
         * @return INT
         * @access PROTECTED
         **/
        public static function generate_seed(){

            list($usec, $sec) = explode(' ', microtime());

            return (float) $sec + ((float) $usec * 100000);

        }

        /**
        * Esse método gera um valor randomico
        *
        * @param   NULL
        * @return  INT
        * @access  public
        *
        **/
        public static function getRandomNumber(){

            $seed    =  self::generate_seed();

            //Alimenta o gerador de seeds do php
            mt_srand( $seed );

            //gera o numero randomico
            return rand(1111111, 9999999);

        }
    }
