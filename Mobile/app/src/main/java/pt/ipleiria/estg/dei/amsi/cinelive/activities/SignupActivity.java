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
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserValidationListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.DataManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class SignupActivity extends AppCompatActivity {

    ActivitySignupBinding binding;
    DataManager manager;

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
        manager = DataManager.getInstance();

        // Configurar os listeners
        setOnClickListeners();
    }

    private User getUser() {
        String username = String.valueOf(binding.form.etUsername.getText());
        String password = String.valueOf(binding.form.etPassword.getText());
        String email = String.valueOf(binding.form.etEmail.getText());
        String nome = String.valueOf(binding.form.etNome.getText());
        String telemovel = String.valueOf(binding.form.etTelemovel.getText());

        // Devolver objeto user
        return new User(username, password, email, nome, telemovel);
    }

    private void setOnClickListeners() {
        // Criar conta
        binding.btnSignup.setOnClickListener(v -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(this)) {
                ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
                finish();
            }

            // Obter e validar dados
            User user = getUser();
            if (!manager.validateFormFields(this, binding.form, user, true)) {
                return;
            }

            // Signup
            signup(user);
        });

        // Iniciar sessão
        binding.btnLogin.setOnClickListener(v -> {
            startActivity(new Intent(this, LoginActivity.class));
            finish();
        });
    }

    private void signup(User user) {
        manager.signup(this, user, new UserValidationListener() {
            @Override
            public void onSuccess() {
                Toast.makeText(getApplicationContext(), R.string.msg_sucesso_signup, Toast.LENGTH_SHORT).show();

                // Reset MainActivity
                Intent intent = new Intent(getApplicationContext(), MainActivity.class);
                intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                startActivity(intent);
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
                Toast.makeText(getApplicationContext(), R.string.msg_erro_signup, Toast.LENGTH_LONG).show();
            }
        });
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}