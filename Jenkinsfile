pipeline {

    agent any

    environment {
        APP_PORT = '18000'
        MOCKSERVER_PORT = '11080'
    }

    stages {

        stage('Check Docker') {
            steps {
                echo 'Verification de la connexion au demon Docker (socket)'
                sh 'docker version'
                sh 'docker compose version'
            }
        }

        stage('Build Docker Image') {
            steps {
                echo 'Construction des images'
                sh 'docker compose build app'
            }
        }

        stage('Start Docker') {
            steps {
                echo 'Demarrage des services'
                sh 'docker compose up -d app mysql mockserver'
            }
        }

        stage('Check Services') {
            steps {
                echo 'Verification des conteneurs'
                sh 'docker compose ps'
            }
        }

        stage('Test MockServer') {
            steps {
                echo 'Verification de mockserver'
                sh 'docker compose exec -T app php scripts/wait_for_http.php http://mockserver:1080/external/products/1 120'
            }
        }

        stage('Run PHPUnit') {
            steps {
                echo 'lancement des tests PHPUnit'
                sh 'docker compose exec -T app vendor/bin/phpunit'
            }
        }
    }

    post {

        failure {
            echo 'Logs des conteneurs au moment de l echec'
            sh 'docker compose logs --tail 50 mockserver app || true'
            echo 'echec'
        }

        success {
            echo 'Termine avec success'
        }

        always {
            echo 'Nettoyage Docker'
        }

        cleanup {
            sh 'docker compose down -v --remove-orphans || true'
        }
    }
}
