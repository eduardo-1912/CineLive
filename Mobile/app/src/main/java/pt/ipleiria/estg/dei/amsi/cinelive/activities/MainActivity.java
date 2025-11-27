package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.navigation.ui.AppBarConfiguration;
import androidx.navigation.ui.NavigationUI;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityMainBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

public class MainActivity extends AppCompatActivity {

    private ActivityMainBinding binding;
    private NavHostFragment navHostFragment;
    private NavController navController;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        // Obter o NavController
        navHostFragment = (NavHostFragment)getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        navController = navHostFragment.getNavController();

        // Configurar
        AppBarConfiguration appBarConfiguration = new AppBarConfiguration.Builder
                (R.id.navFilmes, R.id.navCinemas, R.id.navCompras, R.id.navPerfil).build();

        // Toolbar integrada com o Navigation Component
        setSupportActionBar(binding.toolbar.topAppBar);
        NavigationUI.setupActionBarWithNavController(this, navController, appBarConfiguration);

        // BottomNavigation ligado ao NavController
        NavigationUI.setupWithNavController(binding.bottomNav, navController);

        boolean isLoggedIn = true; // TODO: depois substituir por SharedPreferences/token

        // Mostrar 'Entrar' ou 'Perfil'
        binding.bottomNav.getMenu().findItem(R.id.navPerfil).setTitle(isLoggedIn ? R.string.nav_perfil : R.string.nav_entrar);

        // Mostrar 'Compras' se estiver logged in
        binding.bottomNav.getMenu().findItem(R.id.navCompras).setVisible(isLoggedIn);

        binding.bottomNav.setOnItemSelectedListener(item -> {

            if (item.getItemId() == R.id.navPerfil) {
                if (!isLoggedIn) {
                    // Redirecionar para LoginActivity
                    startActivity(new Intent(this, LoginActivity.class));
                    return false;
                }
            }

            // Comportamento normal do NavigationUI
            NavigationUI.onNavDestinationSelected(item, navController);
            return true;
        });
    }

    @Override
    public void onResume()
    {
        super.onResume();
        if (!NetworkUtils.hasInternet(this)) {
            Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
        }
    }
}