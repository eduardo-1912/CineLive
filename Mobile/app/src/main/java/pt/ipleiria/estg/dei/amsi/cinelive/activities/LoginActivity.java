package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityLoginBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.LoginListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class LoginActivity extends AppCompatActivity {

    private ActivityLoginBinding binding;
    private AuthManager authManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityLoginBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Esconder campos do form
        binding.form.tilEmail.setVisibility(View.GONE);
        binding.form.tilNome.setVisibility(View.GONE);
        binding.form.tilTelemovel.setVisibility(View.GONE);

        // Obter o manager
        authManager = AuthManager.getInstance();
    }

    @Override
    protected void onResume() {
        super.onResume();

        // Botão Iniciar Sessão
        binding.btnLogin.setOnClickListener(v -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(this)) {
                Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
            }

            // Obter dados
            String username = String.valueOf(binding.form.etUsername.getText());
            String password = String.valueOf(binding.form.etPassword.getText());

            // Criar objeto user
            User user = new User(username , password);

            // Validar dados
            if (!validateFields(user)) return;

            // Fazer pedido de login
            authManager.login(this, user, new LoginListener() {
                @Override
                public void onSuccess() {
                    Toast.makeText(LoginActivity.this, R.string.msg_sucesso_login, Toast.LENGTH_SHORT).show();
                    finish();
                }
                @Override
                public void onInvalidCredentials() {
                    Toast.makeText(LoginActivity.this, R.string.msg_credenciais_invalidas, Toast.LENGTH_SHORT).show();
                }
                @Override
                public void onError() {
                    Toast.makeText(LoginActivity.this, R.string.msg_erro_login, Toast.LENGTH_LONG).show();
                }
            });
        });

        // Botão Criar Conta
        binding.btnSignup.setOnClickListener(v -> {
            startActivity(new Intent(LoginActivity.this, SignupActivity.class));
            finish();
        });
    }

    private boolean validateFields(User user) {
        boolean isValid = true;

        // Validar username
        if (user.getUsername().isEmpty()) {
            binding.form.tilUsername.setError(getString(R.string.msg_campo_obrigatorio));
            isValid = false;
        }
        else binding.form.tilUsername.setErrorEnabled(false);

        // Validar password
        if (user.getPassword().isEmpty()) {
            binding.form.tilPassword.setError(getString(R.string.msg_campo_obrigatorio));
            isValid = false;
        }
        else binding.form.tilPassword.setErrorEnabled(false);

        return isValid;
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}