package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivitySignupBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserFormListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class SignupActivity extends AppCompatActivity {

    ActivitySignupBinding binding;
    AuthManager authManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivitySignupBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Obter o manager
        authManager = AuthManager.getInstance();
    }

    @Override
    protected void onResume() {
        super.onResume();

        // Botão Criar Conta
        binding.btnSignup.setOnClickListener(v -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(this)) {
                Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
            }

            // Obter dados
            String username = String.valueOf(binding.form.etUsername.getText());
            String password = String.valueOf(binding.form.etPassword.getText());
            String email = String.valueOf(binding.form.etEmail.getText());
            String nome = String.valueOf(binding.form.etNome.getText());
            String telemovel = String.valueOf(binding.form.etTelemovel.getText());

            // Criar objeto user
            User user = new User(username, password, email, nome, telemovel);

            // Validar dados
            if (!validateFields(user)) return;

            // Fazer pedido de signup
            authManager.signup(this, user, new UserFormListener() {
                @Override
                public void onSuccess() {
                    Toast.makeText(SignupActivity.this, R.string.msg_sucesso_signup, Toast.LENGTH_SHORT).show();
                    finish();
                }

                @Override
                public void onUsernameTaken() {
                    binding.form.tilUsername.setError(getString(R.string.msg_username_indisponivel));
                }

                @Override
                public void onEmailTaken() {
                    binding.form.tilEmail.setError(getString(R.string.msg_email_indisponivel));
                }

                @Override
                public void onError() {
                    Toast.makeText(SignupActivity.this, R.string.msg_erro_signup, Toast.LENGTH_LONG).show();
                }
            });
        });

        // Botão Iniciar Sessão
        binding.btnLogin.setOnClickListener(v -> {
            startActivity(new Intent(SignupActivity.this, LoginActivity.class));
            finish();
        });
    }

    private boolean validateFields(User user) {
        boolean isValid = true;
        int minLengthUsername = AuthManager.MIN_LENGTH_USERNAME;
        int minLengthPassword = AuthManager.MIN_LENGTH_PASSWORD;
        int minLengthTelemovel = AuthManager.MIN_LENGTH_TELEMOVEL;

        // Validar username
        if (user.getUsername().length() < minLengthUsername) {
            binding.form.tilUsername.setError(getString(R.string.msg_min_caracteres, minLengthUsername));
            isValid = false;
        }
        else binding.form.tilUsername.setErrorEnabled(false);

        // Validar password
        if (user.getPassword().length() < minLengthPassword) {
            binding.form.tilPassword.setError(getString(R.string.msg_min_caracteres, minLengthPassword));
            isValid = false;
        }
        else binding.form.tilPassword.setErrorEnabled(false);

        // Validar email
        if (user.getEmail().isEmpty()) {
            binding.form.tilEmail.setError(getString(R.string.msg_campo_obrigatorio));
            isValid = false;
        }
        else if (!android.util.Patterns.EMAIL_ADDRESS.matcher(user.getEmail()).matches()) {
            binding.form.tilEmail.setError(getString(R.string.msg_email_invalido));
            isValid = false;
        }
        else binding.form.tilEmail.setErrorEnabled(false);

        // Validar nome
        if (user.getNome().isEmpty()) {
            binding.form.tilNome.setError(getString(R.string.msg_campo_obrigatorio));
            isValid = false;
        }
        else binding.form.tilNome.setErrorEnabled(false);

        // Validar telemóvel
        if (user.getTelemovel().length() < 9) {
            binding.form.tilTelemovel.setError(getString(R.string.msg_min_caracteres, minLengthTelemovel));
            isValid = false;
        }
        else binding.form.tilTelemovel.setErrorEnabled(false);

        return isValid;
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}