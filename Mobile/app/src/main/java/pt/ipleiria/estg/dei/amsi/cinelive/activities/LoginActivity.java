package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import static android.view.View.GONE;

import android.content.Intent;
import android.os.Bundle;
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

        binding.form.tilEmail.setVisibility(GONE);
        binding.form.tilNome.setVisibility(GONE);
        binding.form.tilTelemovel.setVisibility(GONE);

        authManager = AuthManager.getInstance();

        binding.btnLogin.setOnClickListener(v -> {

            String username = String.valueOf(binding.form.etUsername.getText());
            String password = String.valueOf(binding.form.etPassword.getText());

            authManager.login(this, username, password, new LoginListener() {
                @Override
                public void onSuccess() {
                    finish();
                }

                @Override
                public void onError() {
                    Toast.makeText(LoginActivity.this, "erro", Toast.LENGTH_SHORT).show();
                }
            });
        });

        binding.btnSignup.setOnClickListener(v -> {
            startActivity(new Intent(LoginActivity.this, SignupActivity.class));
            finish();
        });
    }

    @Override
    public void onResume()
    {
        super.onResume();
        if (!ConnectionUtils.hasInternet(this)) {
            finish();
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}